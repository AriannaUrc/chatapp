<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

if ($receiver_id === 0) {
    echo json_encode([]);
    exit();
}

$connection = new mysqli('localhost', 'root', '', 'mychat');
if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

// Fetch sender and receiver usernames
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$sender_result = $stmt->get_result();
$sender = $sender_result->fetch_assoc();
$sender_username = $sender['username'];

$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $receiver_id);
$stmt->execute();
$receiver_result = $stmt->get_result();
$receiver = $receiver_result->fetch_assoc();
$receiver_username = $receiver['username'];

$query = "SELECT sender_ID, msg_content FROM users_chats WHERE (sender_ID = ? AND receiver_ID = ?) OR (sender_ID = ? AND receiver_ID = ?) ORDER BY msg_date ASC";
$stmt = $connection->prepare($query);
$stmt->bind_param('iiii', $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
