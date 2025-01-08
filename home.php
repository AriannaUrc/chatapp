<?php
session_start();
include("include/connection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$get_user = "SELECT * FROM users WHERE user_id = ?";
$stmt = $con->prepare($get_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $user_name = $user['user_name'];
    $user_profile_image = $user['user_profile'];
} else {
    echo "User not found!";
    exit();
}

$receiver_id = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null;
$messages = [];

if ($receiver_id) {
    $messages_query = "SELECT * FROM users_chats WHERE (sender_ID = ? AND receiver_ID = ?) OR (sender_ID = ? AND receiver_ID = ?) ORDER BY msg_id ASC";
    $stmt = $con->prepare($messages_query);
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
    $messages = $messages_result->fetch_all(MYSQLI_ASSOC);
}

if (isset($_POST['logout'])) {
    $update_msg = mysqli_query($con, "UPDATE users SET log_in = 'Offline' WHERE user_id ='$user_id'");
    $run_update = mysqli_query($con, $update_msg);

    session_destroy();
    header("Location: signin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-3 col-xs-12 left-sidebar">
                <div class="input-group searchbox">
                    <div class="input-group-btn">
                        <center><a href="include/find_friends.php"><button class="btn btn-default search-icon" name="search_user" type="submit">Add new user</button></a></center>
                    </div>
                </div>
                <div class="left-chat">
                    <?php
                    $user_list_query = "SELECT * FROM users WHERE user_id != ?";
                    $stmt = $con->prepare($user_list_query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user_list_result = $stmt->get_result();
                    while ($user_data = $user_list_result->fetch_assoc()) {
                        echo "<div class='user-list-item'><a href='home.php?receiver_id=" . $user_data['user_id'] . "'>" . $user_data['user_name'] . "</a></div>";
                    }
                    ?>
                </div>
            </div>

            <div class="col-md-9 col-sm-9 col-xs-12 right-sidebar">
                <div class="right-header">
                    <div class="right-header-img">
                        <img src="<?php echo $user_profile_image; ?>" alt="profile image">
                        <div class="right-header-detail">
                            <form method="POST">
                                <p>Logged in as: <?php echo $user_name; ?></p>
                                <span><?php echo count($messages); ?> messages</span>
                                <button name="logout" class="btn btn-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="right-header-contentChat">
                    <ul id="message-container">
                    <?php foreach ($messages as $message): ?>
                        <div class="rightside-chat" id="message-<?php echo $message['msg_id']; ?>" data-message-id="<?php echo $message['msg_id']; ?>">
                            <span><?php echo $message['sender_ID'] == $user_id ? 'You' : 'User ' . $message['sender_ID']; ?> 
                                <small><?php echo $message['msg_date']; ?></small></span>
                            <p class="message-content"><?php echo $message['msg_content']; ?></p>
                            <?php if ($message['sender_ID'] == $user_id): ?>
                                <button class="edit-button" onclick="editMessage(<?php echo $message['msg_id']; ?>, '<?php echo addslashes($message['msg_content']); ?>')">Edit</button>
                                <button class="delete-button" onclick="deleteMessage(<?php echo $message['msg_id']; ?>)">Delete</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </ul>
                </div>

                <div class="right-chat-textbox">
                    <form id="message-form">
                        <input type="text" id="message-input" name="msg_content" placeholder="Write your message..." required>
                        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-telegram"></i> Send</button>
                    </form>
                    <div id="edit-notification" style="display:none; color: red;">Editing message: <span id="editing-message-id"></span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Socket.io client -->
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <script>
// Setup for socket connection
const socket = io('http://localhost:8080');
const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
const receiverId = <?php echo isset($_GET['receiver_id']) ? json_encode($_GET['receiver_id']) : 'null'; ?>;

// Join the socket with the userId
socket.emit('join', userId);

// Send a message
document.getElementById('message-form').addEventListener('submit', (e) => {
    e.preventDefault();

    const messageContent = document.getElementById('message-input').value;
    if (messageContent && receiverId) {
        // Send the message to the server (no temporary message shown on the client yet)
        socket.emit('send_message', {
            sender_id: userId,
            receiver_id: receiverId,
            message: messageContent
        });

        // Clear input
        document.getElementById('message-input').value = '';
    }
});

// Handle message reception
socket.on('receive_message', (data) => {
    const messageContainer = document.getElementById('message-container');

    // Create the message element
    const messageElement = document.createElement('div');
    messageElement.classList.add('rightside-chat');
    messageElement.setAttribute('id', 'message-' + data.message_id);  // Use the actual message_id
    messageElement.setAttribute('data-message-id', data.message_id);

    messageElement.innerHTML = `
        <span>${data.sender_id === userId ? 'You' : 'User ' + data.sender_id} <small>${data.msg_date}</small></span>
        <p class="message-content">${data.message}</p>
        ${data.sender_id === userId ? `
            <button class="edit-button" onclick="editMessage(${data.message_id}, '${data.message}')">Edit</button>
            <button class="delete-button" onclick="deleteMessage(${data.message_id})">Delete</button>
        ` : ''}
    `;
    
    messageContainer.appendChild(messageElement);
    messageContainer.scrollTop = messageContainer.scrollHeight;
});

// Edit message function
function editMessage(messageId, currentMessage) {
    const newMessage = prompt("Edit your message:", currentMessage);
    if (newMessage && newMessage !== currentMessage) {
        socket.emit('edit_message', {
            message_id: messageId,
            new_message: newMessage,
            sender_id: userId,
            receiver_id: receiverId
        });
    }
}

// Delete message function
function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        socket.emit('delete_message', { message_id: messageId, sender_id: userId, receiver_id: receiverId });
    }
}

// Handle edit message update from server
socket.on('edit_message', (data) => {
    const messageElement = document.getElementById('message-' + data.message_id);
    if (messageElement) {
        messageElement.querySelector('.message-content').textContent = data.new_message;
    }
});

// Handle delete message update from server
socket.on('delete_message', (data) => {
    const messageElement = document.getElementById('message-' + data.message_id);
    if (messageElement) {
        messageElement.remove();
    }
});
</script>
</body>
</html>

