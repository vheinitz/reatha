var express = require('express');
var request = require('request');
var async = require('async');
var format = require('string-format')
var moment = require('moment');
var fs = require('fs');
var bodyParser = require('body-parser')
var jsonParser = bodyParser.json({ type: 'application/*+json' } );

var router = express.Router();

/* GET home page. */
router.get('/', function(req, res, next) {
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
	 
});

router.get('/api/instrument/:id', function (req, res) {
    return list_entries(q_select_ws("instrument", req.body, "instrument_access_key"), req, res);
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


