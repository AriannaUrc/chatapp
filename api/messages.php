<?php
include('../include/connection.php');
header('Content-Type: application/json');

// Retrieve the Socket.IO server address and port (make sure this matches your Socket.IO server)
define('SOCKET_SERVER', 'http://localhost:8080');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Handle sending a message
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Ensure all necessary data is provided
    if (isset($data['sender_id'], $data['receiver_id'], $data['message'])) {
        $sender_id = $data['sender_id'];
        $receiver_id = $data['receiver_id'];
        $message = htmlentities($data['message']);
        
        // Insert message into database
        $query = "INSERT INTO users_chats (sender_ID, receiver_ID, msg_content, msg_status) VALUES (?, ?, ?, 'unread')";
        $stmt = $con->prepare($query);
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        
        if ($stmt->execute()) {
            // Get inserted message ID
            $message_id = $stmt->insert_id;

            // Prepare the data for broadcasting to Socket.IO
            $socket_data = [
                'message_id' => $message_id,
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'message' => $message
            ];

            // Send data to the Socket.IO server via HTTP request
            $ch = curl_init(SOCKET_SERVER . "/emit");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($socket_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            curl_close($ch);

            // Return success response
            echo json_encode(['status' => 'success', 'message' => 'Message sent successfully', 'message_id' => $message_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    }
} elseif ($method == 'PUT') {
    // Handle updating a message
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['message_id'], $data['new_message'])) {
        $message_id = $data['message_id'];
        $new_message = htmlentities($data['new_message']);

        $query = "UPDATE users_chats SET msg_content = ? WHERE message_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("si", $new_message, $message_id);
        
        if ($stmt->execute()) {
            // Emit event to Socket.IO server (broadcast message edit)
            $socket_data = [
                'message_id' => $message_id,
                'new_message' => $new_message
            ];

            // Send data to the Socket.IO server via HTTP request
            $ch = curl_init(SOCKET_SERVER . "/emit-edit");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($socket_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            curl_close($ch);

            echo json_encode(['status' => 'success', 'message' => 'Message updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update message']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    }
} elseif ($method == 'DELETE') {
    // Handle deleting a message
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['message_id'])) {
        $message_id = $data['message_id'];

        $query = "DELETE FROM users_chats WHERE message_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $message_id);
        
        if ($stmt->execute()) {
            // Emit event to Socket.IO server (broadcast message delete)
            $socket_data = ['message_id' => $message_id];

            // Send data to the Socket.IO server via HTTP request
            $ch = curl_init(SOCKET_SERVER . "/emit-delete");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($socket_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            curl_close($ch);

            echo json_encode(['status' => 'success', 'message' => 'Message deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete message']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
