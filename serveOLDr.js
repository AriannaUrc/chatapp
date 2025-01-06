const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql');

// Create an HTTP server
const server = http.createServer();
const io = socketIo(server, {
    cors: {
        origin: "http://localhost", // Allow connections from your front-end origin
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type"],
        credentials: true
    }
});

// MySQL Database connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root', // Replace with your DB username
    password: '', // Replace with your DB password
    database: 'mychat' // Replace with your DB name
});

db.connect((err) => {
    if (err) throw err;
    console.log('Connected to MySQL database');
});

// Store user sockets in memory
let userSockets = {};

io.on('connection', (socket) => {
    console.log('A user connected');

    // Handle user joining chat
    socket.on('join', (userId) => {
        userSockets[userId] = socket.id;
        console.log(`User ${userId} joined with socket id ${socket.id}`);
    });

    // Handle sending messages
    socket.on('send_message', (data) => {
        const { sender_id, receiver_id, message } = data;
        
        // Save the message to the database
        const query = 'INSERT INTO users_chats (sender_ID, receiver_ID, msg_content, msg_status, msg_date) VALUES (?, ?, ?, "unread", NOW())';
        db.query(query, [sender_id, receiver_id, message], (err, result) => {
            if (err) throw err;

            console.log('Message saved to database');
            
            // Broadcast message to the recipient if they are connected
            if (userSockets[receiver_id]) {
                io.to(userSockets[receiver_id]).emit('receive_message', {
                    sender_id,
                    message
                });
            }
        });
    });

    // Handle user disconnection
    socket.on('disconnect', () => {
        for (let userId in userSockets) {
            if (userSockets[userId] === socket.id) {
                delete userSockets[userId];
                break;
            }
        }
        console.log('A user disconnected');
    });
});

// Start the server
server.listen(3000, () => {
    console.log('WebSocket server running on http://localhost:3000');
});
