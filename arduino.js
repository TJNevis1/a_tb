//var app = require('../../../nodejs/node_modules/express').createServer();
//var io = require('../../../nodejs/node_modules/socket.io').listen(app);
//app.listen(8000);

//It seems the code above is the older version...you get warning messages to switch to this...
var express = require('../../../nodejs/node_modules/express');
var app = express(),
http = require('http'),
server = http.createServer(app),
io = require('../../../nodejs/node_modules/socket.io').listen(server);

server.listen(8000);


// routing
app.get('/', function (req, res) {
  res.sendfile(__dirname + '/arduino.html');
});

app.get('/arduino-socketio/:rfid_serial/:query_string', function (req, res) {
  //res.sendfile(__dirname + '/arduino.html');
  io.sockets.emit('arduinoUpdate', req.params['rfid_serial'], req.params['query_string']); //or req.param('query_string');
});

// usernames which are currently connected to the chat
var usernames = {};

io.sockets.on('connection', function (socket) {

	// when the client emits 'adduser', this listens and executes
	socket.on('adduser', function(username){
		// we store the username in the socket session for this client
		socket.username = username;
		// add the client's username to the global list
		usernames[username] = username;

		io.sockets.emit('arduinoUpdate', socket.username, 'Welcome ' + socket.username + '!');

//socket.broadcast.emit('arduinoUpdate', socket.username, 'Welcome ' + socket.username + '!'); 
	});

	// when the client (arduino-socketio-test.php) emits 'sendUpdate', this listens and executes
	socket.on('sendUpdate', function (rfid_serial, result) {
		io.sockets.emit('arduinoUpdate', rfid_serial, result);

		//console.log(socket.username);

		//io.sockets.emit will send to all the clients
		//socket.broadcast.emit will send the message to all the other clients except the newly created connection

		console.log('arduino.js console.log ' + rfid_serial + ' ' + result);
	});
});