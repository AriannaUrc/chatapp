<?php
include("include/connection.php");

if (isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    
    // Update message status to 'read'
    $update_status = "UPDATE users_chats SET msg_status = 'read' WHERE msg_id = ?";
    $stmt = $con->prepare($update_status);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    
    echo "Message marked as read.";
}
?>
