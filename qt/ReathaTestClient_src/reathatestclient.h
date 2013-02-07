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
    void startRequest(QUrl url);
    void httpFinished();
    void httpReadyRead();

    void on_bSend_clicked();

    void on_bClearLog_clicked();

    void on_bRunStopScript_clicked(bool checked);

    void onVarValueChanges(QString, QVariant);

    void on_bOnOff_clicked();

private:
    Ui::ReathaTestClient *ui;
    QTimer _rmLifeCheckTimer;
    QUrl _url;
    QNetworkAccessManager _qnam;
    QStandardItemModel _currentValues;
    TScript _script;
};

#endif // REATHATESTCLIENT_H
