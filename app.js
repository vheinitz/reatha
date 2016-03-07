var express = require('express');
var multer = require('multer');
var path = require('path');
var favicon = require('serve-favicon');
var logger = require('morgan');

var fs = require('fs');
var app = express();

var jsonfile = require('jsonfile')
var util = require('util')

var file = 'api.json'

stubs = {}


app.use(favicon(__dirname + '/frontend/favicon.ico'));

app.use(express.static(path.join(__dirname, 'frontend')));


app.use(function(req, res, next) {
   console.log( req.path );
    url = req.path;
	jsonfile.readFile(file, function (err, obj) {
		console.dir(obj)
		stubs = obj;
		for ( si in stubs )
		{
			s = stubs[si];
			if ( s.url == req.path )
			{
			    console.log("URL:", s.url);
			    console.log("RESP:", s.resp);
				return res.json(s.resp);
			}		
		}
		
		var results = { status: "ERROR" };
		return res.json(results);

		res.render(json(stubs));	
		})	
});


// error handlers

// development error handler
// will print stacktrace
if (app.get('env') === 'development') {
  app.use(function(err, req, res, next) {
    res.status(err.status || 500);
    res.render('error', {
      message: err.message,
      error: err
    });
  });
}

// production error handler
// no stacktraces leaked to user
app.use(function(err, req, res, next) {
  res.status(err.status || 500);
  res.render('error', {
    message: err.message,
    error: {}
  });
});


var http = require('http');

var port = process.env.PORT || 5000;

app.set('port', port)

var server = http.createServer(app);

/**
 * Listen on provided port, on all network interfaces.
 */

server.listen(port);
server.on('error', onError);
server.on('listening', onListening);

/**
 * Event listener for HTTP server "error" event.
 */

function onError(error) {
    if (error.syscall !== 'listen') {
        throw error;
    }

    var bind = typeof port === 'string'
      ? 'Pipe ' + port
      : 'Port ' + port;

    // handle specific listen errors with friendly messages
    switch (error.code) {
        case 'EACCES':
            console.error(bind + ' requires elevated privileges');
            process.exit(1);
            break;
        case 'EADDRINUSE':
            console.error(bind + ' is already in use');
            process.exit(1);
            break;
        default:
            throw error;
    }
}

/**
 * Event listener for HTTP server "listening" event.
 */

function onListening() {
    var addr = server.address();
    var bind = typeof addr === 'string'
      ? 'pipe ' + addr
      : 'port ' + addr.port;
    //debug('Listening on ' + bind);
}
