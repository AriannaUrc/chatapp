<?php
session_start();
include("include/connection.php");

if (isset($_GET['receiver_id']) && isset($_GET['user_id'])) {
    $receiver_id = $_GET['receiver_id'];
    $user_id = $_GET['user_id'];

    // Fetch the messages between the current user and the receiver
    $messages_query = "SELECT * FROM users_chats WHERE (sender_ID = ? AND receiver_ID = ?) OR (sender_ID = ? AND receiver_ID = ?) ORDER BY msg_date ASC";
    $stmt = $con->prepare($messages_query);
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
    
    $messages = [];
    while ($row = $messages_result->fetch_assoc()) {
        $messages[] = $row;
    }

    // Return messages in HTML format for the chat box
    foreach ($messages as $message) {
        echo "<div class='rightside-chat'>
                <span>" . ($message['sender_ID'] == $user_id ? 'You' : 'User ' . $message['sender_ID']) . " <small>" . $message['msg_date'] . "</small></span>
                <p>" . $message['msg_content'] . "</p>
              </div>";
    }
}
?>
