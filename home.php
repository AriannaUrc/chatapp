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
    $messages_query = "SELECT * FROM users_chats WHERE (sender_ID = ? AND receiver_ID = ?) OR (sender_ID = ? AND receiver_ID = ?) ORDER BY msg_date ASC";
    $stmt = $con->prepare($messages_query);
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
    $messages = $messages_result->fetch_all(MYSQLI_ASSOC);
}

if (isset($_POST['submit'])) {
    $msg = htmlentities($_POST['msg_content']);
    if (!empty($msg) && $receiver_id) {
        $insert_message = "INSERT INTO users_chats (sender_ID, receiver_ID, msg_content, msg_status) VALUES (?, ?, ?, 'unread')";
        $stmt = $con->prepare($insert_message);
        $stmt->bind_param("iis", $user_id, $receiver_id, $msg);
        $stmt->execute();
        
        header("Location: home.php?receiver_id=" . $receiver_id);
        exit();
    }
}

if (isset($_POST['logout'])) {
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
                            <div class="rightside-chat">
                                <span><?php echo $message['sender_ID'] == $user_id ? 'You' : 'User ' . $message['sender_ID']; ?> <small><?php echo $message['msg_date']; ?></small></span>
                                <p><?php echo $message['msg_content']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="right-chat-textbox">
                    <form id="message-form">
                        <input type="text" id="message-input" name="msg_content" placeholder="Write your message..." required>
                        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-telegram"></i> Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Socket.io client -->
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <script>
        const userId = <?php echo $_SESSION['user_id']; ?>;
        let receiverId = <?php echo isset($_GET['receiver_id']) ? $_GET['receiver_id'] : 'null'; ?>;
        
        const socket = io('http://localhost:8080');

        socket.emit('join', userId);

        socket.on('receive_message', (data) => {
            const messageContainer = document.getElementById('message-container');
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.innerHTML = `<span>${data.sender_id === userId ? 'You' : 'User ' + data.sender_id}:</span><p>${data.message}</p>`;
            messageContainer.appendChild(messageElement);
            messageContainer.scrollTop = messageContainer.scrollHeight;
        });

        document.getElementById('message-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const messageContent = document.getElementById('message-input').value;
            if (messageContent && receiverId) {
                socket.emit('send_message', {
                    sender_id: userId,
                    receiver_id: receiverId,
                    message: messageContent
                });

                const messageContainer = document.getElementById('message-container');
                const messageElement = document.createElement('div');
                messageElement.classList.add('message');
                messageElement.innerHTML = `<span>You:</span><p>${messageContent}</p>`;
                messageContainer.appendChild(messageElement);
                messageContainer.scrollTop = messageContainer.scrollHeight;

                document.getElementById('message-input').value = '';
            }
        });
    </script>
</body>
</html>
