#ifndef _FastCgiRequest_HG
#define _FastCgiRequest_HG

#include <QTcpSocket>
#include <QObject>

enum {	 EFcgiBeginRequest=1
		,EFcgiAbortRequest
		,EFcgiEndRequest
		,EFcgiParams
		,EFcgiStdin
		,EFcgiStdout
		,EFcgiStderr
		,EFcgiData
		,EFcgiGetValues
		,EFcgiGetValuesResult
		,EFcgiUnknownType
};


struct FcgiHeader {
            int version;
            int type;
            int requestId; //2B
            int contentLength; //2B
            int paddingLength; //1B
            unsigned char reserved;
};

struct FcgiBeginRequestBody{
    int role; //2B
    int flags; //1B
    unsigned char reserved5;
	unsigned char reserved4;
	unsigned char reserved3;
	unsigned char reserved2;
	unsigned char reserved1;
};

struct FcgiBeginRequestRecord {
    FcgiHeader header;
    FcgiBeginRequestBody body;
};

static const int FcgiKeepConn=1;

enum{	
	EFcgiRoleResponder=1
	,EFcgiRoleAuthorizer
	,EFcgiRoleFilter
};

struct FcgiEndRequestBody
{
    unsigned long appStatus; //4B
    int protocolStatus; //1B
    unsigned char reserved3;
	unsigned char reserved2;
	unsigned char reserved1;
};

struct FcgiEndRequestRecord
{
    FcgiHeader header;
    FcgiEndRequestBody body;
};

struct FastCgiParameter
{
	QString name;
	QString value;
	FastCgiParameter(QString n=QString::null, QString v = QString::null):
		name(n),value(v)
	{
	}
};

typedef QList<FastCgiParameter> TFastCgiParameters;



class FastCgiRequest : public QObject
{
	Q_OBJECT
public:
	FastCgiRequest(QTcpSocket *sock, QObject *p);
	virtual ~FastCgiRequest();
private:
	bool readParameters( int &i, QByteArray &data, TFastCgiParameters &p );
	bool readHeader( int &i, QByteArray &data, FcgiHeader &h );
	int align8Byte(unsigned int n);
	QByteArray makeHeaderData( int type, int req, int len, int plen );
	bool readInput( int &i, QByteArray &data, int len, QByteArray &d );
	bool readBeginRequest( int &i, QByteArray &data, FcgiBeginRequestRecord &br );
	bool readEndRequest( int &i, QByteArray &data, FcgiEndRequestRecord &er );

public slots:
	void prcessData();
	void prcessError(QAbstractSocket::SocketError);

private:
	QTcpSocket *_sock;
};
#endif
