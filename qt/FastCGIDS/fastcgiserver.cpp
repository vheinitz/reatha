#include "fastcgiserver.h"
#include "fastcgirequest.h"
#include <QStringList>


FastCgiServer::FastCgiServer(QObject *p):
	QObject(p)
{
}

FastCgiServer::~FastCgiServer()
{
}

void FastCgiServer::start(unsigned short port)
{
    _srv.listen( QHostAddress::Any, port );
    connect(&_srv, SIGNAL(newConnection()), this, SLOT(processNewConnection()));
}

void FastCgiServer::processNewConnection()
{
	static int cnt=0;
	cnt++;
	FastCgiRequest *fcgirec = new FastCgiRequest( _srv.nextPendingConnection(), this );
	connect (fcgirec, SIGNAL(newRequest()), this, SLOT(processNewRequest()));
}

void FastCgiServer::processNewRequest()
{
	FastCgiRequest *req = qobject_cast<FastCgiRequest*>( sender() );
	if (req)
	{
		QStringList vars = QString(req->_inData).split("&");
		foreach(QString v, vars)
		{
			_deviceVars[ v.section("=",0,0) ] =  v.section("=",1);
		}

		QString data("[");
		for( QMap<QString, QString>::iterator it = _deviceVars.begin(); it != _deviceVars.end(); ++it )
		{
			data+=QString("{\"varName\":\"%1\",\"val\":\"%2\"},").arg( it.key() ).arg(it.value());			
		}
		data +="{}]";
		req->sendData( data );
	}
}