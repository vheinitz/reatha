var express = require('express');
var request = require('request');
var async = require('async');
var format = require('string-format')
var moment = require('moment');
var fs = require('fs');
var bodyParser = require('body-parser')
var jsonParser = bodyParser.json({ type: 'application/*+json' } );
var assert = require('assert');
var router = express.Router();

var devicesData=[];


/* GET home page. */
router.get('/', function(req, res, next) {
  console.log("ROOT");
  
  res.render('index', { title: 'Remote Monitoring' });
});

/*
db.open(function(err, db) {

  // Fetch a collection to insert document into
  db.collection("remove_all_documents_no_safe", function(err, collection) {

    // Insert a bunch of documents
    collection.insert([{a:1}, {b:2}], {w:1}, function(err, result) {
      assert.equal(null, err);

      // Remove all the document
      collection.remove();

      // Fetch all results
      collection.find().toArray(function(err, items) {
        assert.equal(null, err);
        assert.equal(0, items.length);
        db.close();
      });
    });
  })
});
*/

router.post('/api/auth/login/:user/:passwd', function(req, res) {
  console.log('/api/auth/login/:'+req.params.user+'/:'+req.params.passwd)
  var results = { status: "ERROR", data:"err descr" };
  /*mg.connect(dburl, function(err, db) {
	  console.log("URL:", dburl);
	  assert.equal(null, err);
	  console.log("Connected correctly to server.");
	   var cursor =db.collection('rmusers').find( );
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
			 results = {"status":"OK","data":{"session":"ABCDEFG","level":req.params.user}};
			 return res.json(results);			 
			 //return res.json(ans);
		  }
	   });
	});
 */	
  results = {"status":"OK","data":{"session":"ABCDEFG","level":req.params.user}};
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
router.get('/api/instrument/list', function(req, res) {
	console.log('/api/instrument/list')
	res.json(devicesData)
	/*mg.connect(dburl, function(err, db) {
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
	});*/
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

devicesData = 
  [
	{
        _id: "DEV1",
        name: "DEV1",
        type: "HELIOS",
        location: "Location Data",
        info: "some info"
	},
	{
        _id: "DEV2",
        name: "DEV2",
        type: "HELIOS",
        location: "Location Data",
        info: "some info"
	},
	{
        _id: "DEV3",
        name: "DEV3",
        type: "HELIOS",
        location: "Location Data",
        info: "some info"
	},
	{
        _id: "DEV4",
        name: "DEV4",
        type: "HELIOS",
        location: "Location Data",
        info: "some info"
	}
		  
  ]
  
  
////////////////////// END INSTRUMENT ///////////////////////// 
module.exports = router;


