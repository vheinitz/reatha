#include "fastcgiserver.h"

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
	FastCgiRequest *fcgirec = new FastCgiRequest( _srv.nextPendingConnection(), this );
}

