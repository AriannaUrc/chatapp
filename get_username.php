<?php
include("include/connection.php");

if (isset($_GET['sender_id'])) {
    $sender_id = $_GET['sender_id'];
    
    // Fetch sender's username
    $query = "SELECT user_name FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_array();

    if ($row) {
        echo $row['user_name'];
    } else {
        echo "User not found";
    }
}
?>
