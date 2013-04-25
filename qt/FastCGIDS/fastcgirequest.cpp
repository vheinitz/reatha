#include "fastcgirequest.h"
#include <QTimer>

int FastCgiRequest::_uidcnt=0;

FastCgiRequest::FastCgiRequest(QTcpSocket *sock, QObject *p):
	QObject(p)
	,_sock(sock)
	,_reqId(-1)
	,_idx(0)	
{
	connect(_sock,SIGNAL(readyRead()), this, SLOT(processData()));
	connect(_sock,SIGNAL(error(QAbstractSocket::SocketError)), this, SLOT(processError(QAbstractSocket::SocketError)));
	_timeoutTimer = new QTimer(this);
	_timeoutTimer->setInterval(2000);
	_timeoutTimer->setSingleShot(true);
	connect(_timeoutTimer,SIGNAL(timeout()), this, SLOT(processTimeout()));
	_uidcnt++;

}

FastCgiRequest::~FastCgiRequest()
{
	//TODO sock is still child of TcpServer, consider to reparent
	//if (_sock)
	//	_sock->close();
}

bool FastCgiRequest::readParameters( int &i, QByteArray &data, TFastCgiParameters &p )
{
	if (i+1 >= data.size())
		return false;

	while(1){//TODO check for errors
		int nameLen=data.at(i++);
		if ( nameLen == 0 )
			return true;
		if (i+1 >= data.size())
		{
			--i;
			return false;
		}
		int valueLen=data.at(i++);
		if (i+valueLen+nameLen >= data.size())
		{
			--i;
			--i;
			return false;
		}
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
	if (i+len >= data.size())
		return false;
	d= data.mid(i,len);
	i+=len;
	return true;
}

bool FastCgiRequest::readBeginRequest( int &i, QByteArray &data, FcgiBeginRequestRecord &br )
{
	if (i+8 > data.size())
		return false;
	br.body.role = ((unsigned char)data.at(i++)<<8) | (unsigned char)data.at(i++);
	br.body.flags = data.at(i++);
	i+=5; //skip reserved
	return true;
}

bool FastCgiRequest::readEndRequest( int &i, QByteArray &data, FcgiEndRequestRecord &er )
{
	if (i+8 > data.size())
		return false;
	er.body.appStatus = (unsigned char)(data.at(i++)<<24) | (unsigned char)(data.at(i++)<<16) |(unsigned char)(data.at(i++)<<8)| (unsigned char)data.at(i++);
	er.body.protocolStatus = data.at(i++);
	i+=5; //skip reserved
	return true;
}

//TODO make sure no chunk is missing
void FastCgiRequest::processData()
{
	_timeoutTimer->stop();
	static bool readHeader_=true;
    _data += _sock->readAll();
	while(  _idx < _data.size()-1 )
	{
		if (readHeader_)
			if (!readHeader( _idx, _data, _header) )
			{
				_timeoutTimer->start();
				return;
			}

		readHeader_=true;
		switch( _header.type )
		{
			case EFcgiBeginRequest:
				_reqId = _header.requestId;
				if (!readBeginRequest( _idx, _data, _beginReqRecord))
				{
					readHeader_=false;
					_timeoutTimer->start();
					return;			
				}
				break;
			case EFcgiAbortRequest:
			 break;
			case EFcgiEndRequest:
			 break;
			case EFcgiParams:				
				_parameters.clear();
				if ( _header.contentLength > 0 )
					if (!readParameters( _idx, _data, _parameters ))
					{
						_timeoutTimer->start();
						readHeader_=false;
						return;			
					}
			 break;
			case EFcgiStdin:
				if ( _header.contentLength )
					if(!readInput(_idx, _data, _header.contentLength, _inData))
					{
						_timeoutTimer->start();
						readHeader_=false;
						return;			
					}
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
				_idx=_data.size()+1;
				break;
		}			
	}	
	emit newRequest();

}

void FastCgiRequest::sendData( QString data )
{
	_outData = data;
	QTimer::singleShot(10,this, SLOT(processResponse()));
}

void FastCgiRequest::processTimeout()
{
	processResponse();
}

void FastCgiRequest::processResponse()
{
	disconnect(_sock,SIGNAL(readyRead()), this, SLOT(processData()));
	_timeoutTimer->stop();
	QByteArray dout = QString("Content-type: application/json\r\n\r\n%1").arg( _outData ).toUtf8();
	int elen = align8Byte(dout.size());
	int plen = elen-dout.size();
	QByteArray ho = makeHeaderData(EFcgiStdout,_reqId,dout.size(),plen);
	int ti=0;
	readHeader(ti,ho,_header);
    _sock->write(ho);
	_sock->write(dout);
	QByteArray dummy( plen, Qt::Uninitialized );
	_sock->write(dummy);
	ho = makeHeaderData(EFcgiStdout,_reqId,0,0);
	_sock->write(ho);
	ho = makeHeaderData(EFcgiEndRequest,_reqId,0,0);	
	_sock->write(ho);
	ho = makeHeaderData(0,0,0,0);	
	_sock->write(ho);
	_sock->close();
	QTimer::singleShot(500, this, SLOT(close()));
}

void FastCgiRequest::close()
{
	_sock->deleteLater();
	deleteLater();
	_uidcnt++;
	_uidcnt--;
}

void FastCgiRequest::processError(QAbstractSocket::SocketError)
{

}