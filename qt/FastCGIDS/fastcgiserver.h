/* ===========================================================================
 * Copyright 2013: Valentin Heinitz, www.heinitz-it.de
 * FastCGI setver. Manages the connections.
 * Author: Valentin Heinitz, 2013-04-24
 * License: GPL, http://www.gnu.org/licenses/gpl.html
 *
 * D E S C R I P T I O N
 * ongoinf work ...
 ========================================================================== */

#ifndef FASTCGISERVER_H
#define FASTCGISERVER_H

#include "fastcgirequest.h"
#include <QObject>
#include <QTcpServer>
#include <QTcpSocket>
#include <QMap>

class FastCgiServer: public QObject
{
	Q_OBJECT
public:
	FastCgiServer(QObject *p);
	virtual ~FastCgiServer();

public slots:
    void start(unsigned short port);

private slots:
    void processNewConnection();
    void processNewRequest(); //TODO move elsewhere


private:
    QTcpServer _srv;
	QMap<QString, QString> _deviceVars;
};

#endif