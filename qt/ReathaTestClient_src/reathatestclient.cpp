//Retha Test Client
//Reference Client for testing Reatha - remote device monitoring
//Copyright 2013, Valentin Heinitz
//vheinitz@googlemail.com, 2013-02-7

#include "reathatestclient.h"
#include "ui_reathatestclient.h"
#include "persistence.h"

#include <QMessageBox>
#include <QDateTime>
#include <QNetworkReply>


ReathaTestClient::ReathaTestClient(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::ReathaTestClient)
{
    ui->setupUi(this);
    PERSISTENCE_INIT("Reatha","Test Client");
    PERSISTENT("devname",ui->eDeviceName,"text");
    PERSISTENT("devkey",ui->eDeviceKey,"text");
    PERSISTENT("url",ui->eUrl,"text");
    PERSISTENT("script",ui->eScript,"plainText");
	PERSISTENT("singleVarName",ui->processDataNameLineEdit,"text");
	PERSISTENT("singleVarValue",ui->processDataValueLineEdit,"text");
    PERSISTENT("LcTimeout",ui->eLcTimeout,"text");


	_rmLifeCheckTimer.setInterval(ui->eLcTimeout->text().toInt());
	connect(&_rmLifeCheckTimer, SIGNAL(timeout()), this , SLOT(lifeCheck()) );	
    ui->tvCurrentValues->setModel(&_currentValues);
    _currentValues.setColumnCount(2);
}

void ReathaTestClient::onVarValueChanges(QString var, QVariant val)
{
	QString sval = val.toString();
	//ui->eLog->appendPlainText( var+" = "+sval );
	updateData( var,sval );
    for ( int i=0; i< _currentValues.columnCount(); ++i )
    {        
		if ( _currentValues.item(i) && _currentValues.item(i)->data(Qt::DisplayRole).toString() == var )
        {
            _currentValues.setItem(i,1,new QStandardItem(sval));
            return;
        }
    }
    _currentValues.appendRow(
                QList<QStandardItem*>()
                    <<new QStandardItem(var)
                    <<new QStandardItem(sval)
                );	
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
        _rmLifeCheckTimer.setInterval(ui->eLcTimeout->text().toInt());
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
	QString rmUrl = ui->eUrl->text().arg(ui->eDeviceKey->text()).arg("LC").arg( QDateTime::currentMSecsSinceEpoch() );	
    if (rmUrl.isEmpty())
        return;    
	ui->eLog->appendPlainText( rmUrl );
    startRequest( QUrl(rmUrl) );
}

void ReathaTestClient::updateData( QString tagId, QString tagValue )
{
    //_rmLifeCheckTimer.stop();
	QString rmUrl = ui->eUrl->text().arg(ui->eDeviceKey->text()).arg(tagId).arg( tagValue );	
    if (rmUrl.isEmpty())
        return;
	ui->eLog->appendPlainText( rmUrl );
    startRequest( QUrl(rmUrl) );
    //lifeCheck();
    //_rmLifeCheckTimer.start();
}

void ReathaTestClient::startRequest(QUrl url)
{

    QNetworkReply *reply = _qnam.get(QNetworkRequest(url));
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

void ReathaTestClient::on_bClearLog_clicked()
{
    ui->eLog->clear();
}

void ReathaTestClient::on_bRunStopScript_clicked(bool checked)
{
    if (checked)
    {
		
        ui->bRunStopScript->setText(tr("Stop"));
        _currentValues.clear();
		_currentValues.setColumnCount(2);
        
        QStringList tscript = ui->eScript->toPlainText().split("\n");
        foreach( QString sline, tscript )
        {
            QStringList toks = sline.split(";");
            if (toks.size()<3)
                continue; //empty/invalide line
			QString var = toks.at(0);
			int timeStep = toks.at(1).toInt();
			toks.removeFirst();
			toks.removeFirst();
			ScriptData * sd = new ScriptData( var, timeStep, toks );
			connect(sd,SIGNAL(valueChanged(QString,QVariant)), this, SLOT(onVarValueChanges(QString,QVariant)));
			sd->start();
			_script[toks.at(0)] = PScriptData(sd);
        }
    }
    else
    {
		for(TScript::iterator it = _script.begin(); it!=_script.end(); ++it)
		{
			it->data()->stop();
			it->data()->deleteLater();			
		}
		_script.clear();
        ui->bRunStopScript->setText(tr("Run"));
    }

}

void ReathaTestClient::on_bOnOff_clicked()
{

}
