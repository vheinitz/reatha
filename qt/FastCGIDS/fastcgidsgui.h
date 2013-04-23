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
#include <QTcpServer>
#include <QTcpSocket>

namespace Ui {
class FastCgiDsGui;
}

class FastCgiDsGui : public QMainWindow
{
    Q_OBJECT
    
public:
    explicit FastCgiDsGui(QWidget *parent = 0);
    ~FastCgiDsGui();

public slots:
    void start();

private slots:
    void newConection();
    void prcessData();
    void prcessError(QAbstractSocket::SocketError);


private:
    Ui::FastCgiDsGui *ui;
    QTcpServer _srv;
    QTcpSocket *_sock;

};

#endif // FASTCGIDSGUI_H
