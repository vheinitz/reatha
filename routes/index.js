var express = require('express');
var request = require('request');
var async = require('async');
var format = require('string-format');
var moment = require('moment');
var fs = require('fs');
var path = require('path');
var bodyParser = require('body-parser');
var jsonParser = bodyParser.json({ type: 'application/*+json' } );
var assert = require('assert');
var router = express.Router();

var devicesData=[];
var instruments=
	[
		{ key:"u1/device_1"}
		,{ key:"u1/device_2"}
		,{ key:"u1/device_3"}
		,{ key:"u2/device_1"}
		,{ key:"u2/device_2"}
		,{ key:"u2/device_3"}
		,{ key:"u3/device_1"}
		,{ key:"u3/device_2"}
		,{ key:"u3/device_3"}
	];

var sessions=
	[
		{ id:"u1", user: "u1"}
	];

function userOfSession(session_id) {
	return "u1";
}


/* GET home page. */
router.get('/', function(req, res, next) {
  console.log("ROOT");
  
  res.render('index', { title: 'Remote Monitoring' });
});

router.post('/api/auth/login/:user/:passwd', function(req, res) {
  console.log('/api/auth/login/:'+req.params.user+'/:'+req.params.passwd)
  var results = { status: "ERROR", data:"err descr" };
  results = {"status":"OK","data":{"session_id":"u1","level":req.params.user}};
  return res.json(results);
});

router.get('/api/auth/logout/:session_id', function (req, res) {
	console.log('Session:', req.params.session_id);
    var results = { status: "OK" };
    return res.json(results);
});

////////////////////// INSTRUMENT /////////////////////////////
router.post('/api/instrument/list/:session_id', function(req, res) {
	console.log('/api/instrument/list/:')
	console.log('Session:', req.params.session_id);
	
	
	try{
		user = userOfSession( req.params.session_id );
		if ( user === '' )
			throw("Session Error");
			 
		var filename = "frontend/data/" + user + "/devices.json";
		console.log(filename);
		fs.readFile(filename,'utf8', function read(err, data) {
			if (err) {
				throw err;
			}
			console.log( "Data:", data );			
			return res.end(data);
		});
	}
	catch(exception)
	{
		console.log( "Ex:", exception );
		var results = { status: "ERROR", data:"err descr" };
		return res.json(results);
	}
});

router.post('/api/instrument/list/view/:session_id', function(req, res) {
	console.log('/api/instrument/list/view:')
	
    var filename = "frontend/device-list.html";
	console.log(filename);
	try{
		fs.readFile(filename,'utf8', function read(err, data) {
			if (err) {
				throw err;
			}
			console.log( "Data:", data );			
			return res.end(data);
		});
	}
	catch(exception)
	{
		console.log( "Ex:", exception );
		var results = { status: "ERROR", data:"err descr" };
		return res.json(results);
	}
});

router.post('/api/instrument/view/:session_id/:id', function(req, res) {
	console.log('/api/instrument/view/:session_id/:id')
	
    var filename = "frontend/device-detail.html";
	console.log(filename);
	try{
		fs.readFile(filename,'utf8', function read(err, data) {
			if (err) {
				throw err;
			}
			console.log( "Data:", data );			
			return res.end(data);
		});
	}
	catch(exception)
	{
		console.log( "Ex:", exception );
		var results = { status: "ERROR", data:"err descr" };
		return res.json(results);
	}
});

router.post('/api/instrument/data/:session_id/:id', function(req, res) {
	console.log('/api/instrument/data/:session_id/:id')
	
    var filename = "frontend/data/u1/device_1.json";
	console.log(filename);
	try{
		fs.readFile(filename,'utf8', function read(err, data) {
			if (err) {
				throw err;
			}
			//console.log( "Data:", data );			
			return res.end(data);
		});
	}
	catch(exception)
	{
		console.log( "Ex:", exception );
		var results = { status: "ERROR", data:"err descr" };
		return res.json(results);
	}
});


/*
curl -X POST -H "Content-Type: application/json" -d "{\"name\":\"Device X\", \"status\":\"ERR\", \"current_wl\":\"123\"}" 127.0.0.1/api/instrument/set/u1__device_1
*/
router.post('/api/instrument/set/:key', function(req, res) {
	console.log('/api/instrument/set/:key', req)
	
    var filename = "frontend/data/" + req.params.key.replace(/__/g, '/') + ".json";
	console.log(filename);
	try{
		data=JSON.stringify(req.body);
		fs.writeFile( filename, data, function(err) {
			if(err) {
				var results = { status: "ERROR", data: err };
				return res.json(results);
			}
			console.log("data saved!");
			//console.log( "Data:", data );			
			return res.json( { status: "OK" } );
		}); 
	}
	catch(exception)
	{
		console.log( "Ex:", exception );
		var results = { status: "ERROR", data: exception };
		return res.json(results);
	}
});

  
////////////////////// END INSTRUMENT ///////////////////////// 
module.exports = router;
