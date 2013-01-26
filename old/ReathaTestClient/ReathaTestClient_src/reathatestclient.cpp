//Retha Test Client
//Reference Client for testing Reatha - remote device monitoring
//Copyright 2013, Valentin Heinitz
//vheinitz@googlemail.com, 2013-01-16

#include "reathatestclient.h"
#include "ui_reathatestclient.h"

#include <QMessageBox>
#include <QDateTime>
#include <QNetworkReply>

QString rmUrl = "http://reatha.de/rm/updatedevice.php";

ReathaTestClient::ReathaTestClient(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::ReathaTestClient)
{
    ui->setupUi(this);
	connect(&_rmLifeCheckTimer, SIGNAL(timeout()), this , SLOT(lifeCheck()) );
	_rmLifeCheckTimer.setInterval(3000);	
}

ReathaTestClient::~ReathaTestClient()
{
    delete ui;
}

void ReathaTestClient::on_actionExit_triggered()
{
    close();
}

void ReathaTestClient::on_actionAbout_Qt_triggered()
{
	QMessageBox::aboutQt(this);
}

void ReathaTestClient::on_bOnOff_clicked(bool checked)
{
    if (checked)
    {
		ui->bOnOff->setText(tr("OFF"));
		_rmLifeCheckTimer.start();
    }
    else
    {
		ui->bOnOff->setText(tr("ON"));
		_rmLifeCheckTimer.stop();
    }
}

void ReathaTestClient::on_actionAbout_triggered()
{
    QMessageBox::about(this,"Retha Test Client","Reference Client for testing Reatha - remote device monitoring\nCopyright 2013, Valentin Heinitz\nvheinitz@googemail.com");
}

void ReathaTestClient::lifeCheck()
{

    if (rmUrl.isEmpty())
        return;
    QString deviceId = ui->eDeviceName->text();
    QString url = QString("%1?deviceId=%2&tagId=lc&tagValue=%3")
        .arg(rmUrl)
        .arg(deviceId)
        .arg(QDateTime::currentMSecsSinceEpoch());
    startRequest( QUrl(url) );
}

void ReathaTestClient::updateData( QString tagId, QString tagValue )
{
    _rmLifeCheckTimer.stop();
    if (rmUrl.isEmpty())
        return;
    QString deviceId = ui->eDeviceName->text();
    QString url = QString("%1?deviceId=%2&tagId=%3&tagValue=%4")
        .arg(rmUrl)
        .arg(deviceId)
        .arg(tagId)
        .arg(tagValue);
    startRequest( QUrl(url) );
    lifeCheck();
    _rmLifeCheckTimer.start();
}

void ReathaTestClient::startRequest(QUrl url)
{

    QNetworkReply *reply = qnam.get(QNetworkRequest(url));
    connect(reply, SIGNAL(finished()),
         this, SLOT(httpFinished()));
    connect(reply, SIGNAL(readyRead()),
         this, SLOT(httpReadyRead()));

}

void ReathaTestClient::httpFinished()
{
    QNetworkReply *reply = qobject_cast<QNetworkReply*>(sender());
    if (!reply) return;
    static bool failedOnce=false;
    return;
    if ( !failedOnce && reply->error()) {
        failedOnce=true;
        QMessageBox::information(this, tr("RM Error"),
                              tr("RM Access failed: %1.")
                              .arg(reply->errorString()));
    }
    reply->deleteLater();
}

 void ReathaTestClient::httpReadyRead()
 {
    QNetworkReply *reply = qobject_cast<QNetworkReply*>(sender());
    reply->readAll();
 }

void ReathaTestClient::on_bSend_clicked()
{
    updateData( ui->processDataNameLineEdit->text(), ui->processDataValueLineEdit->text() );
}
