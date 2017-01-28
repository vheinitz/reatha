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


/* GET home page. */
router.get('/', function(req, res, next) {
  console.log("ROOT");
  
  res.render('index', { title: 'Remote Monitoring' });
});

router.post('/api/auth/login/:user/:passwd', function(req, res) {
  console.log('/api/auth/login/:'+req.params.user+'/:'+req.params.passwd)
  var results = { status: "ERROR", data:"err descr" };
  results = {"status":"OK","data":{"session_id":"ABCDEFG","level":req.params.user}};
  return res.json(results);
});

router.get('/api/auth/logout/:session_id', function (req, res) {
    var results = { status: "OK" };
    return res.json(results);
});

router.get('/api/auth/connect', function (req, res) {

    var json = list_entries(q_select_j1_w("instrument_session", "instrument", req, "instrument_access_key"), req, res);
    console.log("/api/auth/connect sessions", json)
    var results = { status: "OK", "data": { session: "ABCDEFG" } };
    return res.json(results);
});

router.get('/api/auth/disconnect', function (req, res) {

    var results = { status: "OK" };
    return res.json(results);
});

////////////////////// INSTRUMENT /////////////////////////////
router.post('/api/instrument/list/:session_id', function(req, res) {
	console.log('/api/instrument/list/:')
	
    var filename = "frontend/data/u1/devices.json";
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

router.post('/api/instrument/:id/view/:session_id', function(req, res) {
	console.log('/api/instrument/:id/view/:session_id')
	
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

router.post('/api/instrument/:id/data/:session_id', function(req, res) {
	console.log('/api/instrument/:id/data/:session_id')
	
    var filename = "frontend/data/u1/device_1.json";
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

router.get('/api/instrument/types', function(req, res) {
    return list_entries( q_select( "instrument_type" ), req, res ); 
});

router.post('/api/instrument/add', function(req, res) {
    return add_entries( q_insert( req.body, "instrument", {types:["s","i","s","j"],names:["instrument_name","instrument_type_pkref","instrument_access_key","instrument_info"]} ), req, res );      
});


router.get('/api/instrument/delete', function(req, res) {
    return delete_entries( q_delete_by_pk( req.query, "instrument" ), req, res );      
});

router.post('/api/instrument/update', function(req, res) {
    console.log('/api/instrument/update');
    return update_entries( q_update( req.body, "instrument", {types:["s","i","s","j"],names:["instrument_name","instrument_type_pkref","instrument_access_key","instrument_info"]} ), req, res );      
});
  
  
////////////////////// END INSTRUMENT ///////////////////////// 
module.exports = router;
