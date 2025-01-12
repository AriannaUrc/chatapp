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
    $messages_query = "SELECT uc.msg_id, uc.sender_ID, uc.receiver_ID, uc.msg_content, uc.msg_image, uc.msg_date,
                 u1.user_name AS sender_name, u2.user_name AS receiver_name
          FROM users_chats uc
          JOIN users u1 ON uc.sender_ID = u1.user_id
          JOIN users u2 ON uc.receiver_ID = u2.user_id
          WHERE (uc.sender_ID = ? AND uc.receiver_ID = ?) OR (uc.sender_ID = ? AND uc.receiver_ID = ?)
          ORDER BY uc.msg_date ASC";

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
        <div class="left-sidebar">
            <!-- Add New User by Email -->
            <div class="input-group searchbox">
                <form method="POST" action="add_contact.php">
                    <input type="email" name="email" class="form-control" placeholder="Add new contact by email" required>
                    <button type="submit" class="btn btn-default search-icon" name="add_contact">Add</button>
                </form>
            </div>

            <!-- Display the contact list -->
            <div class="left-chat">
                <?php
                // Fetch contacts (users who are connected with the logged-in user)
                $user_list_query = "SELECT users.user_id, users.user_name, users.user_profile, users.log_in FROM users 
                                    INNER JOIN user_contacts ON user_contacts.contact_id = users.user_id
                                    WHERE user_contacts.user_id = ?";
                $stmt = $con->prepare($user_list_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user_list_result = $stmt->get_result();
                
                while ($user_data = $user_list_result->fetch_assoc()) {
                    echo "
                    <div class='user-list-item'>
                        <div class='user-profile'>
                            <img src='{$user_data['user_profile']}' alt='Profile Picture' class='user-img'>
                            <div class='user-info'>
                                <a href='home.php?receiver_id={$user_data['user_id']}' class='user-name'>{$user_data['user_name']}</a>
                                <p class='user-status'>{$user_data['log_in']}</p>
                            </div>
                        </div>
                    </div>";
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
                        <span><?php echo $message['sender_ID'] == $user_id ? 'You' : $message['sender_name']; ?> 
                            <small><?php echo $message['msg_date']; ?></small></span>
                        <p class="message-content"><?php echo $message['msg_content']; ?></p>
                        <?php if ($message['sender_ID'] == $user_id): ?>
                            <button class="edit-button" onclick="editMessage(<?php echo $message['msg_id']; ?>, '<?php echo addslashes($message['msg_content']); ?>')">Edit</button>
                            <button class="delete-button" onclick="deleteMessage(<?php echo $message['msg_id']; ?>)">Delete</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </ul>
            

            <!-- Image preview container -->
            <div id="image-preview-container" style="display: none; position: relative; margin-top: 10px;">
                    <img id="image-preview" src="" alt="Image preview" style="max-width: 200px; margin-top: 10px; border: 1px solid #ccc; padding: 5px;">
                    <!-- Close button (X) -->
                    <button type="button" id="remove-image" style="position: absolute; top: 5px; right: 5px; background: red; color: white; border: none; border-radius: 50%; padding: 5px; font-size: 12px;">
                        X
                    </button>
                </div>
            </div>
            
            

            <div class="right-chat-textbox" style="display: <?php echo isset($receiver_id) ? 'block' : 'none'; ?>;">
            <form id="message-form" class="message-form">
                <input type="text" id="message-input" name="msg_content" placeholder="Write your message...">
                
                <label for="message-file" class="file-label">Img</label>
                <input type="file" id="message-file" name="message-file" class="file-input" accept="image/*">
                
                
                
                <button type="submit" name="submit" class="btn btn-primary send-button">
                    <i class="fa fa-telegram"></i>&#10148;
                </button>
            </form>
            </div>
        </div>
    </div>
<script>
// Get references to the input, image preview elements, and the close button
const fileInput = document.getElementById('message-file');
const imagePreviewContainer = document.getElementById('image-preview-container');
const imagePreview = document.getElementById('image-preview');
const removeImageButton = document.getElementById('remove-image');

// Add event listener to the file input to handle file selection
fileInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        
        // Set up the reader to display the image once it's loaded
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreviewContainer.style.display = 'block'; // Show the preview container
        };
        
        // Read the selected file as a data URL (base64 string)
        reader.readAsDataURL(file);
    } else {
        // If the selected file is not an image, hide the preview container
        imagePreviewContainer.style.display = 'none';
    }
});

// Add event listener to the remove button to clear the image preview
removeImageButton.addEventListener('click', function() {
    imagePreview.src = ''; // Clear the image preview
    imagePreviewContainer.style.display = 'none'; // Hide the preview container
    fileInput.value = ''; // Clear the file input
});
</script>

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

    // Use the sender's username in the message
    messageElement.innerHTML = `
        <span>${data.sender_id === userId ? 'You' : data.sender_name} <small>${data.msg_date}</small></span>
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

