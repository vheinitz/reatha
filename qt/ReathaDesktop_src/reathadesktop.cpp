#include "reathadesktop.h"
#include "ui_reathadesktop.h"

#include <QMap>
#include <QNetworkReply>
#include <QUuid>
#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonObject>

QMap <int,QString> _ItemNames;
QMap <int,QWidget*> _ItemViews;
QMap <int,QStringList> _ItemDownloadAPI;
QMap <QString,TItemType> _CreateOnResponse;

QString ReathaDesktopVersion = "0.0.1";

ReathaDesktop::ReathaDesktop(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::ReathaDesktop)
{
    ui->setupUi(this);
    _projects.setColumnCount(1);
    _projects.setHorizontalHeaderLabels(QStringList()<<"Projects");
    ui->tvPrjView->setModel(&_projects);
	_ItemNames[EProject] = "Project";
	_ItemNames[EDomain] = "Domain";
	_ItemNames[EDevice] = "Device";
	_ItemNames[EView] = "View";
	_ItemNames[EVariable] = "Variable";
	_ItemNames[ETransformation] = "Transformation";
	_ItemNames[ENotificationRule] = "Notification Rule";
	_ItemNames[EUser] = "User";
	_ItemNames[EUserNotification] = "User Notification";
	_ItemNames[EDomainAdmin] = "Domain Admin";
	_ItemNames[EImage] = "Image";

	_ItemViews[EProject] = ui->wpProject;
	_ItemViews[EDomain] = ui->wpDomain;
	_ItemViews[EDevice] = ui->wpDevice;
	_ItemViews[EView] = ui->wpView;
	_ItemViews[EVariable] = ui->wpVariable;
	_ItemViews[ETransformation] = ui->wpTransformation;
	_ItemViews[ENotificationRule] = ui->wpNotificationRule;
	_ItemViews[EUser] = ui->wpUser;
	_ItemViews[EUserNotification] = ui->wpUserNotification;
	_ItemViews[EDomainAdmin] = ui->wpDomainAdmin;
	_ItemViews[EImage] = ui->wpImage;

/*
*list domains      GET /api/list_domains
 *list users        GET /api/list_users/domain_id/<domain_id>
 *list devices      GET /api/list_devices/domain_id/<domain_id>
 *list views        GET /api/list_views/device_id/<device_id>
 *list variables    GET /api/list_variables/device_id/<device_id>
 *list transformations     GET/api/list_transformations/device_id/<device_id>
 *list notification rules  GET /api/list_notification_rules/device_id/<device_id>
*/

    _ItemDownloadAPI[EProject] << "POST: list_domains"
                               << "POST: list_domain_admins";

    _ItemDownloadAPI[EDomain]  << "POST domain_id=<id>: list_users"
                               << "POST domain_id=<id>: list_devices"
                               << "POST domain_id=<id>: list_images";

    _ItemDownloadAPI[EDevice]  << "POST device_id=<id>: list_views"
                               << "POST device_id=<id>: list_notifications"
                               << "POST device_id=<id>: list_variables"
                               << "POST device_id=<id>: list_transformations"
                               << "POST device_id=<id>: get_list_view";

    _CreateOnResponse["list_domains"] = EDomain;
    _CreateOnResponse["list_domain_admins"] = EDomainAdmin;
    _CreateOnResponse["list_users"] = EUser;
    _CreateOnResponse["list_devices"] = EDevice;
    _CreateOnResponse["list_images"] = EImage;
    _CreateOnResponse["list_views"] = EView;
    _CreateOnResponse["list_notifications"] = ENotificationRule;
    _CreateOnResponse["list_transformations"] = ETransformation;


/*	_ItemDownloadAPI[EView] = "View";
	_ItemDownloadAPI[EVariable] = "Variable";
    _ItemDownloadAPI[ETransfformation] = "Transformation";
	_ItemDownloadAPI[ENotificationRule] = "Notification Rule";
	_ItemDownloadAPI[EUser] = "User";
	_ItemDownloadAPI[EUserNotification] = "User Notification";
	_ItemDownloadAPI[EDomainAdmin] = "Domain Admin";
	_ItemDownloadAPI[EImage] = "Image";
	*/

	this->setWindowTitle(QString("Reatha Desktop. Version: %1").arg(ReathaDesktopVersion) );



   
}

ReathaDesktop::~ReathaDesktop()
{
    delete ui;
}

void ReathaDesktop::on_tvPrjView_customContextMenuRequested(const QPoint &pos)
{
    _curmi = ui->tvPrjView->indexAt( pos );
    QMenu *ctx = new QMenu(this);
    if (_curmi.isValid())
    {
        int itemType = _curmi.data(EIRType).toInt();

        switch( itemType )
        {
            case EProject:
				ctx->addAction(ui->actionNew_Domain);
				ctx->addAction(ui->actionDownload_Project);
            break;
            case EDomain:
				ctx->addAction(ui->actionNew_Domain_Admin);
				ctx->addAction(ui->actionNew_Device);
				ctx->addAction(ui->actionDownload_Project);
            break;
            case EDevice:
				ctx->addAction(ui->actionNew_View);
				ctx->addAction(ui->actionNew_Variable);
				ctx->addAction(ui->actionNew_Notification_Rule);
                ctx->addAction(ui->actionDownload_Project);
            break;
            case EView:
            break;
            case EVariable:
            break;
            case ETransformation:
            break;
            case ENotificationRule:
            break;
            case EUser:
            break;
            case EUserNotification:
            break;
            case EDomainAdmin:
            break;
            case EImage:
            break;
            default:
                ctx->addAction(ui->actionNew_project);
            break;
        }
    }
    else
    {
        ctx->addAction(ui->actionNew_project);
    }
	ctx->move(this->mapToGlobal(pos));
	ctx->move(ctx->x(),ctx->y() + ctx->height() );
    ctx->show();
}

void ReathaDesktop::on_actionExit_triggered()
{
    close();
}

void ReathaDesktop::on_actionNew_project_triggered()
{
	 newItem(EProject);
}

void ReathaDesktop::on_tvPrjView_clicked(const QModelIndex &index)
{
	_curmi = index;
    if (_curmi.isValid())
    {
        int itemType = _curmi.data(EIRType).toInt();

        switch( itemType )
        {
            case EProject:
				ui->swEditViews->setCurrentWidget(ui->wpProject);
            break;
            case EDomain:
				ui->swEditViews->setCurrentWidget(ui->wpDomain);
            break;
            case EDevice:
            break;
            case EView:
            break;
            case EVariable:
            break;
            case ETransformation:
            break;
            case ENotificationRule:
            break;
            case EUser:
            break;
            case EUserNotification:
            break;
            case EDomainAdmin:
            break;
            case EImage:
            break;
            default:
                ui->swEditViews->setCurrentWidget(ui->wpCommon);
            break;
        }
    }
	else
	{
		ui->swEditViews->setCurrentWidget(ui->wpCommon);
	}
   
}

void ReathaDesktop::on_bSelectProjectDir_clicked()
{

}

void ReathaDesktop::on_bDownloadProject_clicked()
{

}

void ReathaDesktop::on_bUploadProject_clicked()
{

}

void ReathaDesktop::on_actionNew_Domain_triggered()
{
	newItem(EDomain);
}

void ReathaDesktop::on_actionNew_Device_triggered()
{
	newItem(EDevice);
}

void ReathaDesktop::on_actionNew_View_triggered()
{
	newItem(EView);
}

void ReathaDesktop::on_actionNew_Domain_Admin_triggered()
{
	newItem(EDomainAdmin);
}

void ReathaDesktop::on_actionNew_User_triggered()
{
	newItem(EUser);
}

void ReathaDesktop::on_actionNew_Variable_triggered()
{
	newItem(EVariable);
}

void ReathaDesktop::on_actionNew_Image_triggered()
{
	newItem(EImage);
}

void ReathaDesktop::on_actionNew_Notification_Rule_triggered()
{
	newItem(ENotificationRule);
}

QMap<QString, QStandardItem *> _uuid2item;

void ReathaDesktop::newItem( TItemType it, const QString & json)
{
	QStandardItem *cur = 0;
	cur = _projects.itemFromIndex(_curmi);
	if( cur )
	{
        QStandardItem *child = new QStandardItem( );		
		cur->appendRow(child);
		_curmi = _projects.indexFromItem(child);
	}
	else
	{
		_projects.insertRow( _projects.rowCount() );
		_curmi = _projects.index( _projects.rowCount()-1,0);
	}

    cur = _projects.itemFromIndex(_curmi);
	_projects.setData( _curmi, _ItemNames[it] );
	_projects.setData( _curmi, it , EIRType );
    QString uuid = QUuid::createUuid().toString();
    _uuid2item[uuid] = cur;
    _projects.setData( _curmi, uuid, EIRUuid );
	_projects.setData( _curmi, _ItemDownloadAPI[it], EIRDnAPI );
    _projects.setData( _curmi, json, EIRData );

	ui->swEditViews->setCurrentWidget(_ItemViews[it]);	
}

void ReathaDesktop::startRequest(QString uuid, QString api, QString data)
{
    QUrl url( ui->eProjectUrl->text() + api );

	QNetworkRequest request(url);
    //request.setHeader(QNetworkRequest::ContentTypeHeader, "application/x-www-form-urlencoded");
    ui->eLog->append( "TX: " + url.toString() + "POST: "+ data );
    QNetworkReply *reply = _qnam.post( request, data.toUtf8() );
	reply->setProperty("APIRequest",api);
	reply->setProperty("APICaller",uuid);

    connect(reply, SIGNAL(finished()),
         this, SLOT(httpFinished()));
    connect(reply, SIGNAL(readyRead()),
         this, SLOT(httpReadyRead()));

}

bool ReathaDesktop::setCurrentItem( QString uuid )
{
    //QList<QStandardItem *> allItems = _projects.findItems (".*", Qt::MatchRegExp );
    //for (int i=0; i<allItems.size(); ++i)
    {
        QStandardItem * it =  _uuid2item[uuid];
    //	if ( it->data(EIRUuid) == uuid )
		{
			_curmi = _projects.indexFromItem( it );
			return true;
		}
	}
	return false;
}

void ReathaDesktop::httpFinished()
{
    QNetworkReply *reply = qobject_cast<QNetworkReply*>(sender());
    if (!reply) 
		return;
    
    reply->deleteLater();
}

 void ReathaDesktop::httpReadyRead()
 {
    QNetworkReply *reply = qobject_cast<QNetworkReply*>(sender());
	QString apiRequest = reply->property("APIRequest").toString();
    QString response = reply->readAll();
	ui->eLog->append( "RX: " + apiRequest +"  "+response );
	
    if (_CreateOnResponse.contains(apiRequest))
    {
        QJsonParseError err;
        QJsonArray domains = QJsonDocument::fromJson(response.toUtf8(), &err).array();

        if ( err.error == QJsonParseError::NoError )
        {

            for (QJsonArray::iterator jit=domains.begin(); jit != domains.end();++jit)
            {
                QJsonObject jo;
                jo =  (*jit).toObject() ;
                if ( setCurrentItem( reply->property( "APICaller" ).toString() ) )
                {
                    QJsonDocument jd(jo);
                    newItem( _CreateOnResponse[apiRequest], jd.toJson() );
                    on_actionDownload_Project_triggered();
                }
            }
        }
    }
    /*else if ( apiRequest == "list_domains" )
	{
        QJsonParseError err;
        QJsonArray domains = QJsonDocument::fromJson(response.toUtf8(), &err).array();

        if ( err.error == QJsonParseError::NoError )
        {

            for (QJsonArray::iterator jit=domains.begin(); jit != domains.end();++jit)
            {
                QJsonObject jo;
                jo =  (*jit).toObject() ;
                if ( setCurrentItem( reply->property( "APICaller" ).toString() ) )
                {
                    QJsonDocument jd(jo);
                    newItem( EDomain, jd.toJson() );
                    on_actionDownload_Project_triggered();
                }
            }
        }
	}
    else if ( apiRequest == "list_domain_admins" )
    {
        QJsonParseError err;
        QJsonArray json = QJsonDocument::fromJson(response.toUtf8(), &err).array();

        if ( err.error == QJsonParseError::NoError )
        {

            for (QJsonArray::iterator jit=json.begin(); jit != json.end();++jit)
            {
                QJsonObject jo;
                jo =  (*jit).toObject() ;
                if ( setCurrentItem( reply->property( "APICaller" ).toString() ) )
                {
                    QJsonDocument jd(jo);
                    newItem( EDomainAdmin, jd.toJson() );
                    on_actionDownload_Project_triggered();
                }
            }
        }
    }
    else if ( apiRequest == "list_users" )
    {
        QJsonParseError err;
        QJsonArray json = QJsonDocument::fromJson(response.toUtf8(), &err).array();

        if ( err.error == QJsonParseError::NoError )
        {

            for (QJsonArray::iterator jit=json.begin(); jit != json.end();++jit)
            {
                QJsonObject jo;
                jo =  (*jit).toObject() ;
                if ( setCurrentItem( reply->property( "APICaller" ).toString() ) )
                {
                    QJsonDocument jd(jo);
                    newItem( EUser, jd.toJson() );
                    on_actionDownload_Project_triggered();
                }
            }
        }
    }
    else if ( apiRequest == "list_devices" )
    {
        QJsonParseError err;
        QJsonArray json = QJsonDocument::fromJson(response.toUtf8(), &err).array();

        if ( err.error == QJsonParseError::NoError )
        {

            for (QJsonArray::iterator jit=json.begin(); jit != json.end();++jit)
            {
                QJsonObject jo;
                jo =  (*jit).toObject() ;
                if ( setCurrentItem( reply->property( "APICaller" ).toString() ) )
                {
                    QJsonDocument jd(jo);
                    newItem( EDevice, jd.toJson() );
                    on_actionDownload_Project_triggered();
                }
            }
        }
    }

    else if ( apiRequest == "list_views" )
    {
        QJsonParseError err;
        QJsonArray json = QJsonDocument::fromJson(response.toUtf8(), &err).array();

        if ( err.error == QJsonParseError::NoError )
        {

            for (QJsonArray::iterator jit=json.begin(); jit != json.end();++jit)
            {
                QJsonObject jo;
                jo =  (*jit).toObject() ;
                if ( setCurrentItem( reply->property( "APICaller" ).toString() ) )
                {
                    QJsonDocument jd(jo);
                    newItem( EView, jd.toJson() );
                    on_actionDownload_Project_triggered();
                }
            }
        }
    }*/

 }
void ReathaDesktop::on_actionDownload_Project_triggered()
{
	QString uuid = _curmi.data( EIRUuid ).toString();
	QStringList apis = _curmi.data( EIRDnAPI ).toStringList();
    QJsonObject jo = QJsonDocument::fromJson( _curmi.data(EIRData).toByteArray()).object();
	foreach( QString api, apis )
	{
        QString url = api.section(":",1).trimmed();
        QRegExp rx("<([^>]*)>");
        QStringList postvars;
        QString postData = api.section("POST",1).section(":",0,0).trimmed();
        int pos = 0;

        while ((pos = rx.indexIn(api, pos)) != -1) {
            postvars << rx.cap(1);
            pos += rx.matchedLength();
        }

        foreach( QString pdi, postvars )
        {
            QString val;
            if( jo.value(pdi).isDouble() )
                val = QString("%1").arg( jo.value(pdi).toDouble() );
            else
                val = jo.value(pdi).toString();

            postData = postData.replace(QString("<%1>").arg(pdi),val);
        }

        startRequest( uuid, url, postData );
    }
}
