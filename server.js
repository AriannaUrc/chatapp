const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql2');
const express = require('express');
const path = require('path');
const fs = require('fs');

// Create the express app
const app = express();

// Now, create the server (with app passed in as argument)
const server = http.createServer(app);

// Set up socket.io after the server is initialized
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type"],
        credentials: true
    }
});

// Store connected users by their userId
let users = {};

// Set up database connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'mychat'
});

// Serve static files from the "uploads" folder
// Serve static files dynamically from the "uploads" folder
app.get('/uploads/:filename', (req, res) => {
    const filename = req.params.filename;
    const filePath = path.join(__dirname, 'uploads', filename);

    // Check if the file exists before serving it
    fs.exists(filePath, (exists) => {
        if (exists) {
            res.sendFile(filePath); // Serve the file
        } else {
            res.status(404).send('File not found');
        }
    });
});

// Error handling for database connection
db.connect((err) => {
    if (err) {
        console.error('Error connecting to MySQL database:', err.stack);
        return;
    }
    console.log('Connected to MySQL database');
});

function wait(milliseconds) {
    const start = Date.now();
    while (Date.now() - start < milliseconds) {
        // busy-wait loop, effectively blocks the execution
    }
}

// Broadcast and store messages in the database
io.on('connection', (socket) => {
    console.log('A user connected');

    // Join event to register users by their userId
    socket.on('join', (userId) => {
        users[userId] = socket.id;
        console.log(`User ${userId} connected with socket id ${socket.id}`);
    });

    socket.on('upload_file', (data) => {
        // Get the file name and the base64 image data
        const fileName = data.name;
        const base64Data = data.img_base64.replace(/^data:image\/\w+;base64,/, ''); // Clean the base64 string if it includes the mime type
    
        // Create the path for the file
        const filePath = path.join(__dirname, 'uploads', fileName);
    
        // Convert the base64 data to a buffer
        const buffer = Buffer.from(base64Data, 'base64');
    
        // Write the buffer to the uploads folder
        fs.writeFile(filePath, buffer, (err) => {
            if (err) {
                console.error('Error saving the file:', err);
                // Handle the error accordingly (e.g., send a response to the client)
            } else {
                console.log('File successfully saved!');
                // Optionally, send a confirmation back to the client
                socket.emit('file_uploaded', { success: true, fileName });
            }
        });
    });

    // Listen for the 'delete_file' event from the client
    socket.on('delete_file', (data) => {
        const fileName = data.name;

        // Create the file path using the file name
        const filePath = path.join(__dirname, 'uploads', fileName);

        // Check if the file exists
        fs.access(filePath, fs.constants.F_OK, (err) => {
            if (err) {
                console.error('File not found:', err);
                // Optionally, notify the client that the file does not exist
                socket.emit('file_deleted', { success: false, message: 'File not found' });
            } else {
                // If the file exists, delete it
                fs.unlink(filePath, (err) => {
                    if (err) {
                        console.error('Error deleting the file:', err);
                        // Handle the error accordingly (e.g., send a response to the client)
                        socket.emit('file_deleted', { success: false, message: 'Error deleting file' });
                    } else {
                        console.log('File successfully deleted!');
                        // Notify the client that the file was successfully deleted
                        socket.emit('file_deleted', { success: true, fileName });
                    }
                });
            }
        });
    });

    // Send a message to the receiver
    socket.on('send_message', (data) => {
        console.log('Received message:', data);

        // Fetch the sender's username from the database
        const sql = 'SELECT user_name FROM users WHERE user_id = ?';
        db.execute(sql, [data.sender_id], (err, results) => {
            if (err) {
                console.error('Error fetching sender username:', err);
                return;
            }

            const senderName = results[0]?.user_name || 'Unknown User'; // Default to 'Unknown User' if no username is found

            // Insert the message into the database
            const insertSql = 'INSERT INTO users_chats (sender_ID, receiver_ID, msg_content, msg_image) VALUES (?, ?, ?, ?)';
            db.execute(insertSql, [data.sender_id, data.receiver_id, data.message, data.img], (err, results) => {
                if (err) {
                    console.error('Error inserting message into DB:', err);
                    return;
                }

                const message_id = results.insertId;  // Get the generated message ID
                const msg_date = new Date().toLocaleString();  // Current timestamp

                console.log('Message saved to DB:', results);

                // Prepare the message to send back to the client with the actual message ID
                const messageToSend = {
                    message_id,       // The actual message_id from the DB
                    sender_id: data.sender_id,
                    sender_name: senderName,  // Add the sender's username
                    receiver_id: data.receiver_id,
                    message: data.message,
                    img: data.img,
                    msg_date
                };

                // Emit the message to the receiver if they are connected
                if (users[data.receiver_id]) {
                    console.log(`Sending message to user ${data.receiver_id}`);
                    io.to(users[data.receiver_id]).emit('receive_message', messageToSend);
                } else {
                    console.log(`User ${data.receiver_id} is not connected`);
                }

                // Also send the message to the sender (for immediate UI update)
                io.to(users[data.sender_id]).emit('receive_message', messageToSend);
            });
        });
    });

    // Handle delete message event
    socket.on('delete_message', (data) => {
        console.log('Delete message event received:', data);

        // Delete the message from the database
        const deleteSql = 'DELETE FROM users_chats WHERE msg_id = ?';
        db.execute(deleteSql, [data.message_id], (err, results) => {
            if (err) {
                console.error('Error deleting message from DB:', err);
                return;
            }
            console.log('Message deleted from DB:', results);

            // Emit delete event to the client
            io.to(users[data.receiver_id]).emit('delete_message', { message_id: data.message_id });
            io.to(users[data.sender_id]).emit('delete_message', { message_id: data.message_id });
        });
    });

    // Handle edit message event
    socket.on('edit_message', (data) => {
        console.log('Edit message event received:', data);

        // Update the message in the database
        const updateSql = 'UPDATE users_chats SET msg_content = ? WHERE msg_id = ?';
        db.execute(updateSql, [data.new_message, data.message_id], (err, results) => {
            if (err) {
                console.error('Error updating message in DB:', err);
                return;
            }
            console.log('Message updated in DB:', results);

            // Emit the updated message to the sender and receiver
            const updatedMessage = {
                message_id: data.message_id,
                new_message: data.new_message
            };

            io.to(users[data.receiver_id]).emit('edit_message', updatedMessage);
            io.to(users[data.sender_id]).emit('edit_message', updatedMessage);
        });
    });

    // Handle user disconnect
    socket.on('disconnect', () => {
        // Only remove from `users` when user actually disconnects.
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
