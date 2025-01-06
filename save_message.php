<?php
session_start();
include("connection.php");

if (isset($_POST['sender_id']) && isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    // Debugging: Check the values
    error_log("Sender ID: $sender_id, Receiver ID: $receiver_id, Message: $message");

    // Prepare the SQL query to insert the message
    $query = "INSERT INTO users_chats (sender_ID, receiver_ID, msg_content, msg_date) VALUES (?, ?, ?, NOW())";
    
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);

        // Execute the query
        if ($stmt->execute()) {
            echo "Message saved successfully.";
        } else {
            // Output error if execution fails
            error_log("Error executing query: " . $stmt->error);
            echo "Failed to save the message.";
        }
        $stmt->close();
    } else {
        // Output error if prepare statement fails
        error_log("Error preparing query: " . $con->error);
        echo "Failed to prepare the query.";
    }

} else {
    echo "Missing parameters.";
}
?>
