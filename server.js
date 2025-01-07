const WebSocket = require('ws');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const mysql = require('mysql2');

// Create the server
const server = http.createServer();
const io = socketIo(server, {
    cors: {
        origin: "*",  // Adjust this based on your frontend URL
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type"],
        credentials: true  // Allow credentials if needed
    }
});

// Store connected users
let users = {};

// Set up database connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',      // Your database username
    password: '',      // Your database password
    database: 'mychat' // Your database name
});

// Error handling for database connection
db.connect((err) => {
    if (err) {
        console.error('Error connecting to MySQL database:', err.stack);
        return;
    }
    console.log('Connected to MySQL database');
});

// Broadcast and store messages in the database
io.on('connection', (socket) => {
    console.log('A user connected');

    // Join event to register users by their userId
    socket.on('join', (userId) => {
        users[userId] = socket.id;
        console.log(`User ${userId} connected with socket id ${socket.id}`);
    });

    // Send a message to the receiver
    socket.on('send_message', (data) => {
        console.log('Received message:', data);

        // Insert the message into the database
        const sql = 'INSERT INTO users_chats (sender_ID, receiver_ID, msg_content, msg_status) VALUES (?, ?, ?, ?)';
        db.execute(sql, [data.sender_id, data.receiver_id, data.message, 'unread'], (err, results) => {
            if (err) {
                console.error('Error inserting message into DB:', err);
                return;
            }
            console.log('Message saved to DB:', results);

            // Emit the message to the receiver if they are connected
            if (users[data.receiver_id]) {
                console.log(`Sending message to user ${data.receiver_id}`);
                io.to(users[data.receiver_id]).emit('receive_message', data);
            } else {
                console.log(`User ${data.receiver_id} is not connected`);
            }
        });
    });

    // Handle user disconnect
    socket.on('disconnect', () => {
        for (let userId in users) {
            if (users[userId] === socket.id) {
                delete users[userId];
                console.log(`User ${userId} disconnected`);
                break;
            }
        }
    });
});

// Start the WebSocket server on port 8080
server.listen(8080, () => {
    console.log('WebSocket server running on ws://localhost:8080');
});
