#include "reathadesktop.h"
#include <QApplication>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);
    ReathaDesktop w;
    w.show();
    
    return a.exec();
}
