
QT       += core gui network

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

TARGET = ReathaDesktop
TEMPLATE = app

DESTDIR = ../output

SOURCES += main.cpp\
        reathadesktop.cpp

HEADERS  += reathadesktop.h

FORMS    += reathadesktop.ui
