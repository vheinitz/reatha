#Retha Test Client
#Reference Client for testing Reatha - remote device monitoring
#Copyright 2013, Valentin Heinitz
#vheinitz@googlemail.com, 2013-01-16


QT       += core gui xml network

DESTDIR = ../output

TARGET = ReathaTestClient
TEMPLATE = app


SOURCES += main.cpp\
        reathatestclient.cpp

HEADERS  += reathatestclient.h

FORMS    += reathatestclient.ui
