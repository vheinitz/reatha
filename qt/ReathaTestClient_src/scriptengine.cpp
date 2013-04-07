#include "scriptengine.h"


ScriptData::ScriptData(QString name, int timeStep, QStringList values):
    _varName(name),
    _timerStepMs(timeStep),
    _values(values),
    _currentIdx(0)
{
     QObject::connect( &_timer, SIGNAL( timeout() ),this, SLOT(onTimeout() ) );
}

void ScriptData::start()
{
    _timer.start(_timerStepMs);
}

void ScriptData::stop()
{
    _timer.stop();
}

void ScriptData::onTimeout(  )
{    
   emit valueChanged( _varName, _values.at(_currentIdx) );
   ++_currentIdx;
   if ( _currentIdx >= _values.size() )
       _currentIdx = 0;
}

ScriptData::~ScriptData()
{
	stop();
}