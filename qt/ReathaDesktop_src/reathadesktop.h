#ifndef REATHADESKTOP_H
#define REATHADESKTOP_H

#include <QMainWindow>
#include <QStandardItemModel>
#include <QStandardItem>
#include <QNetworkAccessManager>

namespace Ui {
class ReathaDesktop;
}

enum TItemRole
{
    EIRType = Qt::UserRole+1,
	EIRUuid,
    EIRDnAPI,
    EIRData
};

enum TItemType
{
    EProject,
    EDomain,
    EDevice,
    EView,
    EVariable,
    ETransformation,
    ENotificationRule,
    EUser,
    EUserNotification,
    EDomainAdmin,
    EImage
};

class ReathaDesktop : public QMainWindow
{
    Q_OBJECT
    
public:
    explicit ReathaDesktop(QWidget *parent = 0);
    ~ReathaDesktop();
    
private slots:
    void on_tvPrjView_customContextMenuRequested(const QPoint &pos);

    void on_actionExit_triggered();

    void on_actionNew_project_triggered();

    void on_tvPrjView_clicked(const QModelIndex &index);

    void on_bSelectProjectDir_clicked();

    void on_bDownloadProject_clicked();

    void on_bUploadProject_clicked();

    void on_actionNew_Domain_triggered();

    void on_actionNew_Device_triggered();

    void on_actionNew_View_triggered();

    void on_actionNew_Domain_Admin_triggered();

    void on_actionNew_User_triggered();

    void on_actionNew_Variable_triggered();

    void on_actionNew_Image_triggered();

    void on_actionNew_Notification_Rule_triggered();
	void newItem( TItemType it, const QString & json=QString::null);

    void startRequest(QString uuid, QString url, QString postData);
    void httpFinished();
    void httpReadyRead();

	bool setCurrentItem( QString uuid );

    void on_actionDownload_Project_triggered();

private:
    Ui::ReathaDesktop *ui;
    QStandardItemModel _projects;
	QModelIndex _curmi;
	QNetworkAccessManager _qnam;
};

#endif // REATHADESKTOP_H
