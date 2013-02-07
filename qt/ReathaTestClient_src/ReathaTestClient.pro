#Retha Test Client
#Reference Client for testing Reatha - remote device monitoring
#Copyright 2013, Valentin Heinitz
#vheinitz@googlemail.com, 2013-01-16
#Changelog:
# 2013-01-28	Added persistence


QT       += core gui xml network

DESTDIR = ../output

TARGET = ReathaTestClient
TEMPLATE = app


SOURCES += main.cpp\
        reathatestclient.cpp\
		persistence.cpp \
    scriptengine.cpp \
    help.cpp
		

HEADERS  += reathatestclient.h\
            persistence.h \
    scriptengine.h \
    help.h
			

FORMS    += reathatestclient.ui \
    help.ui
