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
#include "fastcgiserver.h"
#include <QTimer>



FastCgiDsGui::FastCgiDsGui(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::FastCgiDsGui),
	_fcgiServer(0)
{
    ui->setupUi(this);
	QTimer::singleShot(100, this, SLOT(start()));
}


void FastCgiDsGui::start()
{
	delete _fcgiServer;
	_fcgiServer = new FastCgiServer(this);
    _fcgiServer->start(9010);
}


FastCgiDsGui::~FastCgiDsGui()
{
    delete ui;
}
