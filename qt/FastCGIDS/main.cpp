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
#include <QApplication>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);
    FastCgiDsGui w;
    w.show();
    
    return a.exec();
}
