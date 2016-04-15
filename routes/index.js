var express = require('express');
var request = require('request');
var async = require('async');
var format = require('string-format')
var moment = require('moment');
var fs = require('fs');
var bodyParser = require('body-parser')
var jsonParser = bodyParser.json({ type: 'application/*+json' } );
var mg = require('mongodb').MongoClient;
var assert = require('assert');

var dburl = 'mongodb://localhost:27017/rm';

var router = express.Router();


/* GET home page. */
router.get('/', function(req, res, next) {
  console.log("ROOT");
  
  res.render('index', { title: 'HERA' });
});

router.get('/api/auth/login', function(req, res) {
  var results = {status:"OK","data":{session:"ABCDEFG"}};
  return res.json(results);
});

router.get('/api/auth/logout', function (req, res) {
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
router.post('/api/instrument/list', function(req, res) {
	console.log('/api/instrument/list')
	mg.connect(dburl, function(err, db) {
	  console.log("URL:", dburl);
	  assert.equal(null, err);
	  console.log("Connected correctly to server.");
	   var cursor =db.collection('rmdevices').find( );
	   var ans = {};
		ans["result"] = "OK";
	   var instr = []
		
	   cursor.each(function(err, doc) {
		  assert.equal(err, null);
		  if (doc != null) {
			 //console.dir(doc);
			 instr.push(doc)
		  } else {
			 ans["instruments"] = instr;
			 console.log(ans);
			 db.close();
			 return res.json(ans);
		  }
	   });
	});
});

router.post('/api/instrument/:id', function (req, res) {
    console.log('/api/instrument/:id', req.params.id)
	mg.connect(dburl, function(err, db) {
	  console.log("URL:", dburl);
	  assert.equal(null, err);
	  console.log("Connected correctly to server.");
	   var cursor =db.collection('rmdevices').find( );
	   var ans = {};
		ans["result"] = "OK";
	   var instr = []
		
	   cursor.each(function(err, doc) {
		  assert.equal(err, null);
		  if (doc != null) {
		     console.log(doc, doc["_id"], req.params.id);
			if (doc["_id"] == req.params.id) {
				ans["data"]=doc;
			}
		  } else {
			 console.log(ans);
			 db.close();
			 return res.json(ans);
		  }
	   });
	});
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
    console.log('/api/instrument/update')
    return update_entries( q_update( req.body, "instrument", {types:["s","i","s","j"],names:["instrument_name","instrument_type_pkref","instrument_access_key","instrument_info"]} ), req, res );      
});

////////////////////// END INSTRUMENT ///////////////////////// 
module.exports = router;


