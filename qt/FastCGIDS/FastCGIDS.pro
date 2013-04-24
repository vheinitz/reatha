
QT       += core gui network sql

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

TARGET = FastCGIDS
TEMPLATE = app



SOURCES += main.cpp\
        fastcgiserver.cpp\
		fastcgirequest.cpp\
		fastcgidsgui.cpp\
		db/dbmanager.cpp

HEADERS  += fastcgidsgui.h\
		fastcgiserver.h\
		fastcgirequest.h\
		db/dbmanager.h

FORMS    += fastcgidsgui.ui
