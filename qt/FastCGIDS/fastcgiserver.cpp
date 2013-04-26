#include "fastcgiserver.h"
#include "fastcgirequest.h"
#include <QStringList>


FastCgiServer::FastCgiServer(QObject *p):
	QObject(p)
{
	//TODO read from persistence
	_deviceVars["12345678"]["v1"];
	_deviceVars["12345678"]["v2"];
	_deviceVars["12345678"]["v3"];
	_deviceVars["12345678"]["v4"];
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
		//TODO use json
		QStringList vars = QString(req->_inData).split("&");
		if (vars.size()>=2) //at lease device/user key and command
		{
			QString key = vars.at(0).section("=",1); //todo check name
			QString cmd = vars.at(1).section("=",1); //todo check name

			QMap<QString, QMap<QString, QString> >::iterator devData = _deviceVars.find( key );
			if ( devData != _deviceVars.end() )
			{
				if ( cmd == "SET" )
				{
					for(int i=2; i< vars.size(); ++i)
					{
						QMap<QString, QString>::iterator varit = devData->find( vars.at(i).section("=",0,0) );
						if( varit != devData->end() )
						{
							varit.value() = vars.at(i).section("=",1);
						}
						else
						{
							req->sendData( "{ \"status\":\"error\", \"description\":\"can't SET, no such PV\" }" );
							return;
						}
					}
					req->sendData( "{ \"status\":\"ok\" }" );
				}
				else if ( cmd == "GET" )
				{
					QString data("{\"status\":\"ok\",\"data\":[");
					for(int i=2; i< vars.size(); ++i)
					{
						QMap<QString, QString>::iterator varit = devData->find( vars.at(i).section("=",0,0) );
						if( varit != devData->end() )
						{
							data+=QString("{\"varName\":\"%1\",\"val\":\"%2\"},").arg( varit.key() ).arg(varit.value());
						}
						else
						{
							req->sendData( "{ \"status\":\"error\", \"description\":\"can't GET, no such PV\" }" );
							return;
						}
					}
					data +="{}]}";
					req->sendData( data );
				}
				else
				{
					req->sendData( "{ \"status\":\"error\", \"description\":\"invalide command\" }" );
				}
			}
			else
			{
				req->sendData( "{ \"status\":\"error\", \"description\":\"invalide key\" }" );
			}						
		}
		else
		{
			req->sendData( "{ \"status\":\"error\", \"description\":\"invalide request\" }" );
		}
	}
}