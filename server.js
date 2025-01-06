const WebSocket = require('ws');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');

// Create the server
const server = http.createServer();
const io = socketIo(server, {
    cors: {
        origin: "http://localhost",  // The frontend URL (if it's running on a different port)
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type"],
        credentials: true  // Allow credentials if needed
    }
});

// Store connected clients
let users = {};

const mysql = require('mysql2');

// Set up database connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',      // Your database username
    password: '',      // Your database password
    database: 'mychat' // Your database name
});

// Broadcast and store messages in the database
io.on('connection', (socket) => {
    console.log('A user connected');

    // Join event to register users by their userId
    socket.on('join', (userId) => {
        users[userId] = socket.id;
        console.log(`User ${userId} connected`);
    });

    // Send a message to the receiver
    socket.on('send_message', (data) => {
        console.log('Received message:', data);

        // Insert the message into the database
        const sql = 'INSERT INTO users_chats (sender_ID, receiver_ID, msg_content, msg_status) VALUES (?, ?, ?, ?)';
        db.execute(sql, [data.sender_id, data.receiver_id, data.message, 'unread'], (err, results) => {
            if (err) {
                console.error('Error inserting message into DB:', err);
            } else {
                console.log('Message saved to DB:', results);
            }
        });

        // Emit the message to the receiver
        if (users[data.receiver_id]) {
            io.to(users[data.receiver_id]).emit('receive_message', data);
            console.log(`Message sent to user ${data.receiver_id}`);
        }
    });

    // Handle user disconnect
    socket.on('disconnect', () => {
        for (let userId in users) {
            if (users[userId] === socket.id) {
                delete users[userId];
                break;
            }
        }
        console.log('User disconnected');
    });
});

// Start the WebSocket server on port 8080
server.listen(8080, () => {
    console.log('WebSocket server running on ws://localhost:8080');
});
