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
#include <QMap>
#include <QSharedPointer>

class QSslError;
class QAuthenticator;
class QNetworkReply;

namespace Ui {
class ReathaTestClient;
}

class ScriptData : public QObject
{
    Q_OBJECT
private:
    QTimer _timer;
public:
    QString _varName;
    QStringList _values;
    int _currentIdx;
    int _timerStepMs;
    ScriptData():_currentIdx(0),_timerStepMs(0){}

    void start()
    {
        _timer.start(_timerStepMs);
    }

    void stop()
    {
        _timer.stop();
    }

    void setHandler( QObject* h )
    {
        QObject::connect( &_timer, SIGNAL( timeout() ) )
    }
};

typedef QSharedPointer<ScriptData> PScriptData;

typedef QMap<QString,PScriptData> TScript;

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
    void startRequest(QUrl url);
    void httpFinished();
    void httpReadyRead();

    void on_bSend_clicked();

    void on_bClearLog_clicked();

    void on_bRunStopScript_clicked(bool checked);

private:
    Ui::ReathaTestClient *ui;
    QTimer _rmLifeCheckTimer;
    QUrl _url;
    QNetworkAccessManager _qnam;
    QStandardItemModel _currentValues;
    TScript _script;
};

#endif // REATHATESTCLIENT_H
