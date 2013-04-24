/* ===========================================================================
 * Copyright 2013: Valentin Heinitz, www.heinitz-it.de
 * Testing implementation of FastCGI in Qt
 * Author: Valentin Heinitz, 2013-04-24
 * License: GPL, http://www.gnu.org/licenses/gpl.html
 *
 * D E S C R I P T I O N
 * ongoinf work ...
 ========================================================================== */

#ifndef FASTCGIDSGUI_H
#define FASTCGIDSGUI_H

#include <QMainWindow>


namespace Ui {
class FastCgiDsGui;
}

class FastCgiServer;

class FastCgiDsGui : public QMainWindow
{
    Q_OBJECT
    
public:
    explicit FastCgiDsGui(QWidget *parent = 0);
    ~FastCgiDsGui();

public slots:
	void start();

private:
    Ui::FastCgiDsGui *ui;
	FastCgiServer *_fcgiServer;
};

#endif // FASTCGIDSGUI_H
