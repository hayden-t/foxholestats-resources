/**
 * See `examples/channels.js` for how to set up SseChannel instances
 * You need to run `npm install express compression` in order for this example to work
 */
'use strict';

//run by supervisor

const settings = require('../foxhole-settings.json');



var express = require('express');
var SseChannel = require('sse-channel');
var https = require('https');
var fs = require('fs');
var os = require('os');
var mysql = require('mysql');
const sizeof = require('object-sizeof')
const compression = require('compression')
const cors = require('cors')

var port = settings.sse_port;
var app = express();

var con = mysql.createConnection({
  host: settings.db_server,
  user: settings.db_username,
  password: settings.db_password,
  database: settings.db_name
});
con.connect(function(err) {
  if (err) throw err;
 }); 	
	 
var eventChannel = new SseChannel({
    retryTimeout: 1000,
    historySize: 0,
    jsonEncode: true,
});
var stateChannel = new SseChannel({
    retryTimeout: 1000,
    historySize: 0,
    jsonEncode: true,
});

var currentEventId = 0;
var currentStateId = 0;

 con.query("SELECT * FROM `warapi_state` WHERE id = 'currentEventId'", function (err, result, fields) {
		//if (err) throw err;						
		if(result.length){
			currentEventId = result[0]['value'];			
		}
		start();
		
});

// Set up an interval that broadcasts system info every 250ms


function start(){

	setInterval(function broadcastEvents() {

		  con.query("SELECT * FROM `warapi_events_latest` WHERE id > "+currentEventId+" ORDER BY `warapi_events_latest`.`id` ASC", function (err, result, fields) {
			if (err) throw err;						
			if(result.length){
			
				for (var i = 0; i < result.length; i++) {
					var viewers = eventChannel.getConnectionCount();
					var data = result[i];
					eventChannel.send({ id:result[i].id , data: data, event: 'event' });
					currentEventId = result[i].id;
					console.log('eventId: '+currentEventId + ', '+ sizeof(data)/1000 + ' Kb, '+ viewers + ' viewers = '+ ((sizeof(data)/1000)*viewers)/1000 + ' Mb');
				}
				
				con.query("REPLACE INTO `warapi_state` (id, value) VALUES  ('currentEventId', "+currentEventId+")", function (err, result, fields) {
					if (err) throw err;
					
				});
					
			}	
			
		  });
		

	}, 20000);

}


	setInterval(function broadcastState() {
	

		  con.query("SELECT `regionId`,`wardenCasualties`,`colonialCasualties`,`wardenRate`,`colonialRate`,`captures`, IF(`regionId` = 0, `time`, 0) as time, IF(`regionId` = 0, `day`, 0) as day, IF(`regionId` = 0, `totalPlayers`, 0) as totalPlayers, IF(`regionId` = 0, `scorchedVictoryTowns`, 0) as scorchedVictoryTowns FROM `warapi_dynamic` WHERE totalEnlistments > 0 AND etag != -1 ORDER BY `regionId`", function (err, result, fields) {
			if (err) throw err;
			if(result.length){
					var viewers = eventChannel.getConnectionCount();
					var data = [result, viewers];
					stateChannel.send({ id: ++currentStateId , data: data, event: 'totals' });
					console.log('stateId: '+currentStateId + ', '+ sizeof(data)/1000 + ' Kb, '+ viewers + ' viewers = '+ ((sizeof(data)/1000)*viewers)/1000 + ' Mb');
					con.query("REPLACE INTO `warapi_state` (id, value) VALUES  ('viewers', "+viewers+")", function (err, result, fields) {
					if (err) throw err;
					
				});					
			}
			
		  });
		

	}, 60000);	

//express webserver//

// Note: Compression is optional and might not be the best idea for Server-Sent Events,
// but this showcases that SseChannel attempts to flush responses as quickly as possible,
// even with compression enabled
app.use(compression());

// Serve static files for the demo
app.use(express.static(settings.path+'/../node_modules/sse-channel/examples/client'));

app.use(cors({
    origin: settings.domain
}));

// The '/channel' prefix is not necessary - you can add any client
// to an SSE-channel, if you want to, regardless of URL
app.get('/channel/eventlog', function(req, res) {
    // Serve the client using the "sysinfo" SSE-channel
	var ip = req.headers['x-forwarded-for'] || req.connection.remoteAddress
    console.log('new connection: '+ ip)        
    eventChannel.addClient(req, res/*, function(error){console.log(error)}*/);

});
app.get('/channel/state', function(req, res) {
    // Serve the client using the "sysinfo" SSE-channel
	//var ip = req.headers['x-forwarded-for'] || req.connection.remoteAddress
  //   console.log('new connection: '+ ip)        
    stateChannel.addClient(req, res/*, function(error){console.log(error)}*/);

});

var options = {
//  ca: [fs.readFileSync(PATH_TO_BUNDLE_CERT_1), fs.readFileSync(PATH_TO_BUNDLE_CERT_2)],
  cert: fs.readFileSync(settings.path+'/../ssl.cert'),
  key: fs.readFileSync(settings.path+'/../ssl.key')
};

var server = https.createServer(options, app);

server.listen(port, function(){
	console.log('Listening on https://localhost:%s/', port)
});
