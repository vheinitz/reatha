#ifndef SCRIPTENGINE_H
#define SCRIPTENGINE_H
#include <QObject>
#include <QTimer>
#include <QStringList>
#include <QSharedPointer>
#include <QMap>
#include <QVariant>

class ScriptData : public QObject
{
    Q_OBJECT
private:
    QTimer _timer;
    QString _varName;
    int _timerStepMs;
    QStringList _values;
    int _currentIdx;
public:

    ScriptData(QString name, int timeStep, QStringList values);
    void start();
    void stop();
    void setHandler( QObject* h );
private slots:
    void onTimeout();
signals:
    void valueChanged( QString, QVariant );
};

typedef QSharedPointer<ScriptData> PScriptData;

typedef QMap<QString,PScriptData> TScript;


#endif // SCRIPTENGINE_H
