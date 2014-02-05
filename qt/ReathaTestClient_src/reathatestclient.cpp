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
#include <QStringListModel>
#include "help.h"

#if QT_VERSION >= QT_VERSION_CHECK(5,0,0)
#include <QUrlQuery>
#endif

const QString ReathaTestClientVersion="0.0.5";

ReathaTestClient::ReathaTestClient(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::ReathaTestClient)
{
    ui->setupUi(this);
    PERSISTENCE_INIT("Reatha","Device Simulator");
    PERSISTENT("devname",ui->eDeviceName,"text");
    PERSISTENT("devkey",ui->eDeviceKey,"text");
    PERSISTENT("url",ui->eUrl,"text");
    PERSISTENT("script",ui->eScript,"plainText");
	PERSISTENT("singleVarName",ui->processDataNameLineEdit,"text");
	PERSISTENT("singleVarValue",ui->processDataValueLineEdit,"text");
    PERSISTENT("LcTimeout",ui->eLcTimeout,"text");
    PERSISTENT("collectTimeout",ui->eCollectTime,"text");


	_rmLifeCheckTimer.setInterval(ui->eLcTimeout->text().toInt());
	connect(&_rmLifeCheckTimer, SIGNAL(timeout()), this , SLOT(lifeCheck()) );
	connect(&_sendDataTimer, SIGNAL(timeout()), this, SLOT(startRequest()) );
	_sendDataTimer.setSingleShot(true);
	_sendDataTimer.setInterval(1000);
	this->setWindowTitle(QString("Reatha Device Simulator. Version: %1").arg(ReathaTestClientVersion) );
    _currentValues.setColumnCount(2);
    _currentValues.setHorizontalHeaderLabels(QStringList()<<tr("Var. name")<<tr("Value"));
	ui->ltCurrentValues->setModel(&_curValues);
}

void ReathaTestClient::onVarValueChanges(QString var, QVariant val)
{
	QString sval = val.toString();
	//ui->eLog->appendPlainText( var+" = "+sval );
	updateData( var,sval );

	QStringList Vars;
    foreach ( QString k, _sendDataList.keys() )
    {        
		Vars << k + "\t\t" + _sendDataList[k];

    }   
	_curValues.setStringList(Vars);
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
	updateData( "lc", "" );
}

void ReathaTestClient::updateData( QString tagId, QString tagValue )
{
	_sendDataList[tagId] = tagValue;
	if (!_sendDataTimer.isActive())
	{
		_sendDataTimer.setInterval( ui->eCollectTime->text().toInt() );
		_sendDataTimer.start();	
	}
}

void ReathaTestClient::startRequest()
{
	QUrl url(ui->eUrl->text());


#if QT_VERSION < QT_VERSION_CHECK(5,0,0)
    QUrl postData;
#else
    QUrlQuery postData;
#endif
	QString js=QString("{\"device\":\"%1\",").arg(ui->eDeviceKey->text());
    //postData.addQueryItem("key", ui->eDeviceKey->text());
	//postData.addQueryItem("cmd", "SET");
	QString vars;
    for( QMap<QString,QString>::iterator it = _sendDataList.begin(); it !=_sendDataList.end();++it )
    {
		if (!vars.isEmpty())
			vars+=',';
		vars +=QString("\"%1\":\"%2\"").arg(it.key()).arg(it.value());
        //postData.addQueryItem( it.key(), it.value() );
    }

	js += QString("\"vars\":{%1}}").arg(vars);

	//postData.addQueryItem( it.key(), it.value() );


	_sendDataList.clear();

	QNetworkRequest request(url);
	request.setHeader(QNetworkRequest::ContentTypeHeader, "application/x-www-form-urlencoded");
	ui->eLog->appendPlainText( "PUT: " + url.toString() );
	ui->eLog->appendPlainText( "  DATA: " + js );	
	QNetworkReply *reply = _qnam.put(request, js.toUtf8() );

    connect(reply, SIGNAL(finished()),
         this, SLOT(httpFinished()));
    connect(reply, SIGNAL(readyRead()),
         this, SLOT(httpReadyRead()));

}

void ReathaTestClient::httpFinished()
{
    QNetworkReply *reply = qobject_cast<QNetworkReply*>(sender());
    if (!reply) 
		return;
    
    reply->deleteLater();
}

 void ReathaTestClient::httpReadyRead()
 {
    QNetworkReply *reply = qobject_cast<QNetworkReply*>(sender());
    ui->eLog->appendPlainText( "GOT DATA: " + reply->readAll() );
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

			_script[var] = PScriptData(sd);
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

void ReathaTestClient::on_actionHelp_triggered()
{
	(new Help)->show();
}
