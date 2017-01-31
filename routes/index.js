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
var lokijs = require('lokijs');
var router = express.Router();


var db = new lokijs('loki.json', 
{
	autosave: true, 
	autosaveInterval: 60000,
	autoload: true,
    autoloadCallback : initDB
});

var users=null;
var devices=null;
var sessions=null;


function test()
{

	s = sessionStart( "u1" );
	console.log( "S ", JSON.stringify( s  ) )
	u = sessionUser(s)
	console.log( "U ",JSON.stringify(  u  ) )
	
	devs = userDevices(u)
	console.log( "Devices ",JSON.stringify( devs  ) )

}

function initDB()
{		
    console.log( "initDB" )
	
	var coll = db.getCollection('users');
    if (coll === null) {
		console.log ("Creating users")
        users = db.addCollection('users')
		users.insert({name:'u1', password: '1'})
		users.insert({name:'u2', password: '2'})
		users.insert({name:'u3', password: '3'})
    }
    
	coll = db.getCollection('devices');
    if (coll === null) {
		console.log ("Creating devices")
        devices = db.addCollection('devices')
		devices.insert({key:'d1', name:'Device1', location:"floor 1", users:['u1'], status: 'OK', current_wl:'123'})
		devices.insert({key:'d2', name:'Device2', location:"floor 2", users:['u1','u2'], status: 'OK', current_wl:'123'})
		devices.insert({key:'d3', name:'Device3', location:"floor 3", users:['u2','u3'], status: 'OK', current_wl:'123'})
		devices.insert({key:'d4', name:'Device4', location:"floor 4", users:['u1','u2','u3','u4'], status: 'OK', current_wl:'123'})
    }
	
	coll = null;//db.getCollection('sessions');
    if (coll === null) {
		console.log ("Creating sessions")
        db.addCollection('sessions')			
    }
	users = db.getCollection('users')
    devices = db.getCollection('devices')
    sessions = db.getCollection('sessions')
	sessions.clear();

	test();
}


function sessionUser(session_id) {
	console.log( "sessionUser( ", session_id , ")" )
	s = sessions.where( function(obj){
		return obj.session_id == session_id;
	})
	console.log( JSON.stringify(s) )
	return s[0].user;	
}

function sessionStart( user ) {
	s = sessions.where( function(obj){	
	  return obj.user == user;
	})
	
	if (s.length) throw("Session for user already exists")
		
	var session_id = 's_'+user;
	sessions.insert({ user:user, session_id:session_id})	
		
	return session_id;	
}

function userDevices(user) {
	console.log(  "userDevices", user )
	devs = devices.where( function(obj){
		return obj.users.indexOf( user ) > -1;
	}) 	
	return devs;	
}



/* GET home page. */
router.get('/', function(req, res, next) {
  console.log("ROOT");
  
  res.render('index', { title: 'Remote Monitoring' });
});

router.post('/api/auth/login/:user/:passwd', function(req, res) {
  console.log('/api/auth/login/:'+req.params.user+'/:'+req.params.passwd)
  var ses = sessionStart( req.params.user );
  results = {"status":"OK","data":{"session_id":ses}};
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
		user = sessionUser( req.params.session_id );
		
		console.log('user:', user);
		if ( user === '' )
			throw("Session Error");
			
		devs =  userDevices( user )
		
		return res.end( JSON.stringify( devs ) )
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
