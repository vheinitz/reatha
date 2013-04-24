#include "fastcgirequest.h"

FastCgiRequest::FastCgiRequest(QTcpSocket *sock, QObject *p):
	QObject(p)
	,_sock(sock)
{
	connect(_sock,SIGNAL(readyRead()), this, SLOT(prcessData()));
	connect(_sock,SIGNAL(error(QAbstractSocket::SocketError)), this, SLOT(prcessError(QAbstractSocket::SocketError)));
}

FastCgiRequest::~FastCgiRequest()
{
	//TODO sock is still child of TcpServer, consider to reparent
	//if (_sock)
	//	_sock->close();
}

bool FastCgiRequest::readParameters( int &i, QByteArray &data, TFastCgiParameters &p )
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

bool FastCgiRequest::readHeader( int &i, QByteArray &data, FcgiHeader &h )
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

int FastCgiRequest::align8Byte(unsigned int n) {
    return (n + 7) & (UINT_MAX - 7);
}

QByteArray FastCgiRequest::makeHeaderData( int type, int req, int len, int plen )
{
	QByteArray h(8,Qt::Uninitialized);
	h[0] = (char)1; //version
	h[1] = (char)type;
	h[2] = (char)((req>>8) & 0xFF);
	h[3] = (char)((req) & 0xFF);
	h[4] = (char)((len>>8) &0xFF);
	h[5] = (char)(len  & 0xFF);
	h[6] = (char)plen;
	h[7] = (char)0;
	return h;
}

bool FastCgiRequest::readInput( int &i, QByteArray &data, int len, QByteArray &d )
{
	d= data.mid(i,len);
	i+=len;
	return true;
}

bool FastCgiRequest::readBeginRequest( int &i, QByteArray &data, FcgiBeginRequestRecord &br )
{
	br.body.role = ((unsigned char)data.at(i++)<<8) | (unsigned char)data.at(i++);
	br.body.flags = data.at(i++);
	i+=5; //skip reserved
	return true;
}

bool FastCgiRequest::readEndRequest( int &i, QByteArray &data, FcgiEndRequestRecord &er )
{
	er.body.appStatus = (unsigned char)(data.at(i++)<<24) | (unsigned char)(data.at(i++)<<16) |(unsigned char)(data.at(i++)<<8)| (unsigned char)data.at(i++);
	er.body.protocolStatus = data.at(i++);
	i+=5; //skip reserved
	return true;
}

void FastCgiRequest::prcessData()
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
	}	
	QByteArray dout = "Content-type: application/json\r\n\r\n{\"var\":\"v1\",\"val\":\"0.4\"}";
	int elen = align8Byte(dout.size());
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

void FastCgiRequest::prcessError(QAbstractSocket::SocketError)
{

}