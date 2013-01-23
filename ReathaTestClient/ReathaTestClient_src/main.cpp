//Retha Test Client
//Reference Client for testing Reatha - remote device monitoring
//Copyright 2013, Valentin Heinitz
//vheinitz@googlemail.com, 2013-01-16

#include <QtGui/QApplication>
#include "reathatestclient.h"

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);
    ReathaTestClient w;
    w.show();
    
    return a.exec();
}
