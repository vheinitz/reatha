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



var db = new lokijs('c:\\tmp\\reatha.json', 
{
	autosave: true, 
	autosaveInterval: 60000,
	autoload: true,
    autoloadCallback : initDB
});

var users=null;
var devices=null;
var sessions=null;

function timestamp()
{
	ts = new Date()	
	return ts.valueOf()
}

function test()
{

	s = sessionStart( "u1" );
	console.log( "S ", JSON.stringify( s  ) )
	u = sessionUser(s)
	console.log( "U ",JSON.stringify(  u  ) )
	
	devs = userDevices(u)
	console.log( "Devices ",JSON.stringify( devs  ) )

}

console.log( timestamp() )

function initDB()
{		
    console.log( "## initDB" )
	
	var coll = db.getCollection('users');
    if (coll === null) {
		console.log ("   Creating users")
        users = db.addCollection('users')
		users.insert({name:'u1', password: '1'})
		users.insert({name:'u2', password: '2'})
		users.insert({name:'u3', password: '3'})
    }
    
	coll = db.getCollection('devices');
    if (coll === null) {
		console.log ("   Creating devices")
        devices = db.addCollection('devices')
		devices.insert({id:'1', key:'d1', name:'Device1', location:"floor 1", users:['u1'], timestamp: '0', online: 0,  data:{}})
		devices.insert({id:'2', key:'d2', name:'Device2', location:"floor 2", users:['u1','u2'], timestamp: '0', online: 0,  data:{}})
		devices.insert({id:'3', key:'d3', name:'Device3', location:"floor 3", users:['u2','u3'], timestamp: '0', online: 0,  data:{}})
		devices.insert({id:'4', key:'d4', name:'Device4', location:"floor 4", users:['u1','u2','u3','u4'],  timestamp: '0', online: 0,  data:{}})
    }
	
	coll = null;//db.getCollection('sessions');
    if (coll === null) {
		console.log ("   Creating sessions")
        db.addCollection('sessions')			
    }
	users = db.getCollection('users')
    devices = db.getCollection('devices')
    sessions = db.getCollection('sessions')
	sessions.clear();

	//test();
}


function sessionUser(session_id) {
	console.log( "## sessionUser( ", session_id , ")" )
	s = sessions.where( function(obj){
		return obj.session_id == session_id;
	})
	console.log( JSON.stringify(s) )
	return s[0].user;	
}


function sessionStart( user ) {
	console.log( "## sessionStart( ", user , ")" )
	s = sessions.where( function(obj){	
	  return obj.user == user;
	})
	
	if (s.length) throw("Session for user already exists")
		
	var session_id = 's_'+user;
	sessions.insert({ user:user, session_id:session_id})	
		
	return session_id;	
}

function sessionStop( session_id ) {
	console.log( "## sessionStop( ", session_id , ")" )
	s = sessions.findOne( { 'session_id' :obj.session_id } )
	s.session_id=''
	sessions.update( s )	
}


function userDevices(user) {
	console.log(  "## userDevices( ", user,")" )
	devs = devices.where( function(obj){
		return obj.users.indexOf( user ) > -1;
	}) 	
	//console.log(  "    ", JSON.stringify(devs) )
	return devs;	
}


/* GET home page. */
router.get('/', function(req, res, next) {
  console.log("---ROOT");
  
  res.render('index', { title: 'Remote Monitoring' });
});

router.post('/api/auth/login/:user/:passwd', function(req, res) {
  console.log('---/api/auth/login/:'+req.params.user+'/:'+req.params.passwd)
  var ses = sessionStart( req.params.user );
  results = {"status":"OK","data":{"session_id":ses}};
  return res.json(results);
});

router.get('/api/auth/logout/:session_id', function (req, res) {
	console.log('---/api/auth/logout/', req.params.session_id);
	sessionStop( req.params.session_id )
    var results = { status: "OK" };
    return res.json(results);
});

////////////////////// INSTRUMENT /////////////////////////////
router.post('/api/instrument/list/:session_id', function(req, res) {
	console.log('---/api/instrument/list/', req.params.session_id)
	console.log("TIMESTAMP: ",timestamp() )
	
	try{
		user = sessionUser( req.params.session_id );
		
		console.log('user:', user);
		if ( user === '' )
			throw("Session Error");
			
		devs =  userDevices( user )
		
		for (dev in devs )
		{
			device  =  devices.findOne( { 'key' : devs[dev].key } )
			//console.log( "   DEVICE:", dev,"  ", device );
			console.log( "   DEVICE TS:", device.timestamp );
			console.log( "   CURR.  TS:", timestamp() );
			console.log( "   TS DIFF:", timestamp() - device.timestamp );
			device.online = (timestamp() - device.timestamp ) < 4000;
			//device.timestamp = timestamp(); 
			devices.update(device);
		}

		return res.end( JSON.stringify( devs ) )
	}
	catch(exception)
	{
		console.log( "Ex:", exception );		
	}
	return res.json({ status: "ERROR", data:"not handled" });
});

router.post('/api/instrument/list/view/:session_id', function(req, res) {
	console.log('---/api/instrument/list/view/',req.params.session_id)
	user = sessionUser( req.params.session_id );
    var filename = "frontend/device-list_" +user+".html";
	if (!fs.existsSync( filename ))
		filename = "frontend/device-list.html";
	
	console.log("  Using view template: ", filename);
	try{
		fs.readFile(filename,'utf8', function read(err, data) {
			if (err) {
				throw err;
			}
			//console.log( "   Data:", data );			
			return res.end(data);
		});
	}
	catch(exception)
	{
		console.log( "   Ex:", exception );
		return res.json({ status: "ERROR", data:"not handled" });
	}
	
});

router.post('/api/instrument/view/:session_id/:id', function(req, res) {
	console.log('---/api/instrument/view/', req.params.session_id,'/', req.params.id)
	
	user = sessionUser( req.params.session_id );
	
    var filename = "frontend/device-detail_"+user+"_"+req.params.id+".html"; //templs_user_deviceid    
	if (!fs.existsSync( filename ))
		filename = "frontend/device-detail_"+user+".html"; //templs_user
		
	if (!fs.existsSync( filename ))
		filename = "frontend/device-detail_"+req.params.id+".html"; //templs_device
		
	if (!fs.existsSync( filename ))
		filename = "frontend/device-detail.html"; // default templs
		
	console.log("  Using view template: ", filename);
	try{
		fs.readFile(filename,'utf8', function read(err, data) {
			if (err) {
				throw err;
			}
			console.log( "   Data:", data );			
			return res.end(data);
		});
	}
	catch(exception)
	{
		console.log( "   Ex:", exception );
		return res.json({ status: "ERROR", data:"not handled" });
	}
	
});

router.post('/api/instrument/data/:session_id/:id', function(req, res) {
	console.log('---/api/instrument/data/', req.params.session_id,'/', req.params.id)
  
	try{
		
		dev = devices.findOne( { 'id': req.params.id } )
		//console.log('   Devices:',  JSON.stringify(devices) )
		//console.log('   Device:',  JSON.stringify(dev) )
		dev.online = (timestamp() - dev.timestamp ) < 4000;
		dev.timestamp = timestamp(); 
		devices.update(dev);
		console.log(   "Device data: ", dev.data);
		return res.end( JSON.stringify( dev.data ) )
	}
	catch(exception)
	{
		console.log( "Ex:", exception );
		return res.json({ status: "ERROR", data:"not handled" });
	}
	
});



/*
c:\bin\curl -X POST -H "Content-Type: application/json" -d "{\"name\":\"Device 4\", \"status\":\"OK\", \"current_wl\":\"123\"}" 127.0.0.1/api/instrument/set/d4
*/
router.post('/api/instrument/set/:key', function(req, res) {
	console.log('---/api/instrument/set/', req.params.key)
	
  
	try{
		dev  =  devices.findOne( { 'key' : req.params.key } )
		console.log('   Device:',  JSON.stringify(dev) )
		console.log('   Body:', JSON.stringify(req.body) )
		dev.data = req.body		
		dev.online = (timestamp() - dev.timestamp ) < 4000;
		dev.timestamp = timestamp(); 
		devices.update(dev);
		return res.json( { status: "OK" } );
	}
	catch(exception)
	{
		console.log( "Ex:", exception );
		return res.json({ status: "ERROR", data:"not handled" });	
	}
	
});

 
////////////////////// END INSTRUMENT ///////////////////////// 
module.exports = router;
