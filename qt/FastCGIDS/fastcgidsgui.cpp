/* ===========================================================================
 * Copyright 2013: Valentin Heinitz, www.heinitz-it.de
 * Testing implementation of FastCGI in Qt
 * Author: Valentin Heinitz, 2013-04-24
 * License: GPL, http://www.gnu.org/licenses/gpl.html
 *
 * D E S C R I P T I O N
 * ongoinf work ...
 ========================================================================== */
#include "fastcgidsgui.h"
#include "ui_fastcgidsgui.h"
#include <QTimer>

enum {	 EFcgiBeginRequest=1
		,EFcgiAbortRequest
		,EFcgiEndRequest
		,EFcgiParams
		,EFcgiStdin
		,EFcgiStdout
		,EFcgiStderr
		,EFcgiData
		,EFcgiGetValues
		,EFcgiGetValuesResult
		,EFcgiUnknownType
};


struct FcgiHeader {
            int version;
            int type;
            int requestId; //2B
            int contentLength; //2B
            int paddingLength; //1B
            unsigned char reserved;
};

struct FcgiBeginRequestBody{
    int role; //2B
    int flags; //1B
    unsigned char reserved5;
	unsigned char reserved4;
	unsigned char reserved3;
	unsigned char reserved2;
	unsigned char reserved1;
};

struct FcgiBeginRequestRecord {
    FcgiHeader header;
    FcgiBeginRequestBody body;
};

#define FCGI_KEEP_CONN  1

#define FCGI_RESPONDER  1
#define FCGI_AUTHORIZER 2
#define FCGI_FILTER     3


struct FcgiEndRequestBody
{
    unsigned long appStatus; //4B
    int protocolStatus; //1B
    unsigned char reserved3;
	unsigned char reserved2;
	unsigned char reserved1;
};

struct FcgiEndRequestRecord
{
    FcgiHeader header;
    FcgiEndRequestBody body;
};

struct FastCgiParameter
{
	QString name;
	QString value;
	FastCgiParameter(QString n=QString::null, QString v = QString::null):
		name(n),value(v)
	{
	}
};

typedef QList<FastCgiParameter> TFastCgiParameters;

bool readParameters( int &i, QByteArray &data, TFastCgiParameters &p )
{
	while(1){//TODO check for errors
		int nameLen=data.at(i++);
		if ( nameLen == 0 )
			return true;
		int valueLen=data.at(i++);
		QString name = data.mid( i, nameLen );	
		i+=nameLen;
		QString value = data.mid( i, valueLen );
		i+=valueLen;
		p.append( FastCgiParameter(name,value) );
	}
	return true;
}

FastCgiDsGui::FastCgiDsGui(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::FastCgiDsGui),
    _sock(0)
{
    ui->setupUi(this);
	QTimer::singleShot(100, this, SLOT(start()));
}

void FastCgiDsGui::start()
{
    _srv.listen(QHostAddress::Any,9010);
    connect(&_srv, SIGNAL(newConnection()), this, SLOT(newConection()));


}

void FastCgiDsGui::newConection()
{
    if (!_sock)
		delete _sock;
    {
        _sock = _srv.nextPendingConnection();
        connect(_sock,SIGNAL(readyRead()), this, SLOT(prcessData()));
        connect(_sock,SIGNAL(error(QAbstractSocket::SocketError)), this, SLOT(prcessError(QAbstractSocket::SocketError)));
    }
}


bool readHeader( int &i, QByteArray &data, FcgiHeader &h )
{
	if (i+8 > data.size())
		return false;
	h.version = data.at(i++);
	h.type = data.at(i++);
	h.requestId = ((unsigned char)data.at(i++)<<8) | (unsigned char)data.at(i++);
	unsigned short lb1= (unsigned char)data.at(i++);
	unsigned short lb0= (unsigned char)data.at(i++);
	h.contentLength = (lb1<<8);
	h.contentLength	|= lb0;
	h.paddingLength = data.at(i++);
	h.reserved = data.at(i++);
	return true;
}

int AlignInt8(unsigned int n) {
    return (n + 7) & (UINT_MAX - 7);
}

/*
int version;
            int type;
            int requestId; //2B
            int contentLength; //2B
            int paddingLength; //1B
            unsigned char reserved;
*/
QByteArray makeHeaderData( int type, int req, int len, int plen )
{
	QByteArray h(8,Qt::Uninitialized);
	h[0] = (char)1; //version
	h[1] = (char)type;
	h[2] = (char)((req>>8) & 0xFF);
	h[3] = (char)((req) & 0xFF);
	h[4] = (char)((len>>8) &0xFF);
	h[5] = (char)(len  & 0xFF);
	h[6] = (char)plen; //TODO
	h[7] = (char)0;
	return h;
}

bool readInput( int &i, QByteArray &data, int len, QByteArray &d )
{
	d= data.mid(i,len);
	i+=len;
	return true;
}

bool readBeginRequest( int &i, QByteArray &data, FcgiBeginRequestRecord &br )
{
	//if (!readHeader(i,data,br.header))
	//	return false;
	br.body.role = ((unsigned char)data.at(i++)<<8) | (unsigned char)data.at(i++);
	br.body.flags = data.at(i++);
	i+=5; //skip reserved
	return true;
}

bool readEndRequest( int &i, QByteArray &data, FcgiEndRequestRecord &er )
{
	//if (!readHeader(i,data,br.header))
	//	return false;
	er.body.appStatus = (unsigned char)(data.at(i++)<<24) | (unsigned char)(data.at(i++)<<16) |(unsigned char)(data.at(i++)<<8)| (unsigned char)data.at(i++);
	er.body.protocolStatus = data.at(i++);
	i+=5; //skip reserved
	return true;
}

void FastCgiDsGui::prcessData()
{
    QByteArray data = _sock->readAll();
	FcgiHeader h;
	FcgiBeginRequestRecord br;
	TFastCgiParameters p;	
	QByteArray d;
	int reqId=0;
	int i=0;
	const char *pbegin=data.constData();
	const char *dp=data.constData();
	//bool stopReading=false;
	while(  i < data.size() )
	{
		
		readHeader( i, data, h );
		dp=pbegin+i;
		switch( h.type )
		{
			case EFcgiBeginRequest:
				reqId = h.requestId;
				readBeginRequest( i,data, br);
				break;
			case EFcgiAbortRequest:
			 break;
			case EFcgiEndRequest:
			 break;
			case EFcgiParams:				
				p.clear();
				if ( h.contentLength > 0 )
					readParameters( i,data,p );
			 break;
			case EFcgiStdin:
				readInput(i,data,h.contentLength, d);
			 break;
			case EFcgiStdout:
			 break;
			case EFcgiStderr:
			 break;
			case EFcgiData:
			 break;
			case EFcgiGetValues:
			 break;
			case EFcgiGetValuesResult:
			 break;
			case EFcgiUnknownType:
			default:
				i=data.size()+1;
				break;
		}
			
		//ui->eLog->appendPlainText(h.contentData);
		ui->eLog->appendPlainText(QString("=======REQ:%1=====").arg(h.requestId));

	}	
	QByteArray dout = "Content-type: application/json\r\n\r\n{\"var\":\"v1\",\"val\":\"0.4\"}";
	int elen = ::AlignInt8(dout.size());
	int plen = elen-dout.size();
	QByteArray ho = makeHeaderData(EFcgiStdout,reqId,dout.size(),plen);
	int ti=0;
	readHeader(ti,ho,h);
    _sock->write(ho);
	_sock->write(dout);
	QByteArray dummy( plen, Qt::Uninitialized );
	_sock->write(dummy);
	ho = makeHeaderData(EFcgiStdout,reqId,0,0);
	_sock->write(ho);
	ho = makeHeaderData(EFcgiEndRequest,reqId,0,0);	
	_sock->write(ho);
	ho = makeHeaderData(0,0,0,0);	
	_sock->write(ho);
	_sock->close();
	//_sock->deleteLater();
	_sock=0;

}

void FastCgiDsGui::prcessError(QAbstractSocket::SocketError)
{

}

FastCgiDsGui::~FastCgiDsGui()
{
    delete ui;
}
