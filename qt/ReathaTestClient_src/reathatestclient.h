//Retha Test Client
//Reference Client for testing Reatha - remote device monitoring
//Copyright 2013, Valentin Heinitz
//vheinitz@googlemail.com, 2013-01-16

#ifndef REATHATESTCLIENT_H
#define REATHATESTCLIENT_H

#include <QMainWindow>
#include <QNetworkAccessManager>
#include <QUrl>
#include <QTimer>
#include <QStandardItemModel>
#include <QStringListModel>
#include <QMap>
#include <QSharedPointer>
#include <scriptengine.h>

class QSslError;
class QAuthenticator;
class QNetworkReply;

namespace Ui {
class ReathaTestClient;
}


class ReathaTestClient : public QMainWindow
{
    Q_OBJECT
    
public:
    explicit ReathaTestClient(QWidget *parent = 0);
    ~ReathaTestClient();
    
private slots:
    void on_actionExit_triggered();

    void on_actionAbout_Qt_triggered();

    void on_bOnOff_clicked(bool checked);

    void on_actionAbout_triggered();

    void lifeCheck();
    void updateData( QString data, QString value );
    void startRequest();
    void httpFinished();
    void httpReadyRead();

    void on_bSend_clicked();

    void on_bClearLog_clicked();

    void on_bRunStopScript_clicked(bool checked);

    void onVarValueChanges(QString, QVariant);

    void on_actionHelp_triggered();

private:
    Ui::ReathaTestClient *ui;
    QTimer _rmLifeCheckTimer;
	QTimer _sendDataTimer;
    QNetworkAccessManager _qnam;
    QStandardItemModel _currentValues;
    TScript _script;
	QMap<QString,QString> _sendDataList;
	QStringListModel _curValues;
};

#endif // REATHATESTCLIENT_H
