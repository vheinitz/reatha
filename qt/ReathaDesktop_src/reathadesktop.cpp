#include "reathadesktop.h"
#include "ui_reathadesktop.h"

#include <QMap>
#include <QNetworkReply>
#include <QUuid>

QMap <int,QString> _ItemNames;
QMap <int,QWidget*> _ItemViews;
QMap <int,QStringList> _ItemDownloadAPI;

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

	_ItemDownloadAPI[EProject] << "list_domains";
	_ItemDownloadAPI[EDomain] << "list_users/domain_id/<domain_id>" << "list_devices/domain_id/<domain_id>";
/*	_ItemDownloadAPI[EDevice] << ;
	_ItemDownloadAPI[EView] = "View";
	_ItemDownloadAPI[EVariable] = "Variable";
	_ItemDownloadAPI[ETransformation] = "Transformation";
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

	_projects.setData( _curmi, _ItemNames[it] );
	_projects.setData( _curmi, it , EIRType );
	_projects.setData( _curmi, QUuid::createUuid().toString(), EIRUuid );
	_projects.setData( _curmi, _ItemDownloadAPI[it], EIRDnAPI );
	ui->swEditViews->setCurrentWidget(_ItemViews[it]);	
}

void ReathaDesktop::startRequest(QString uuid, QString api)
{
	QUrl url( ui->eProjectUrl->text() + api );

	QNetworkRequest request(url);
	request.setHeader(QNetworkRequest::ContentTypeHeader, "application/x-www-form-urlencoded");
	ui->eLog->append( "TX: " + url.toString() );
	QNetworkReply *reply = _qnam.get(request);
	reply->setProperty("APIRequest",api);
	reply->setProperty("APICaller",uuid);

    connect(reply, SIGNAL(finished()),
         this, SLOT(httpFinished()));
    connect(reply, SIGNAL(readyRead()),
         this, SLOT(httpReadyRead()));

}

bool ReathaDesktop::setCurrentItem( QString uuid )
{
	QList<QStandardItem *> allItems = _projects.findItems (".*", Qt::MatchRegExp );
	for (int i=0; i<allItems.size(); ++i)
	{
		QStandardItem * it =  allItems.at(i);
		if ( it->data(EIRUuid) == uuid )
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
	
	
	if ( apiRequest == "list_domains" )
	{
		QStringList domains = response.split( QRegExp("\\}"),QString::SkipEmptyParts );
		foreach( QString data, domains )
		{
			if ( data.contains("\"id\":") )
			{
				if ( setCurrentItem( reply->property( "APICaller" ).toString() ) )
				{
					newItem( EDomain, data );
				}
			}
		}
	}
 }
void ReathaDesktop::on_actionDownload_Project_triggered()
{
	QString uuid = _curmi.data( EIRUuid ).toString();
	QStringList apis = _curmi.data( EIRDnAPI ).toStringList();
	foreach( QString api, apis )
	{
		startRequest( uuid, api.replace("<domain_id>","10") );
	}
}
