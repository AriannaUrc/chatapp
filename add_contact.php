<?php
session_start();
include("include/connection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if (isset($_POST['add_contact'])) {
    $email = $_POST['email'];

    // Check if the email exists in the users table
    $check_user_query = "SELECT user_id, user_name, user_profile FROM users WHERE user_email = ?";
    $stmt = $con->prepare($check_user_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $contact_id = $user_data['user_id'];
        $contact_name = $user_data['user_name'];
        $contact_profile = $user_data['user_profile'];

        // Check if the user is already a contact
        $check_contact_query = "SELECT * FROM user_contacts WHERE user_id = ? AND contact_id = ?";
        $stmt = $con->prepare($check_contact_query);
        $stmt->bind_param("ii", $user_id, $contact_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows == 0) {
            // Add to the user's contact list
            $insert_contact_query = "INSERT INTO user_contacts (user_id, contact_id) VALUES (?, ?)";
            $stmt = $con->prepare($insert_contact_query);
            $stmt->bind_param("ii", $user_id, $contact_id);
            $stmt->execute();

            $_SESSION['message'] = "Contact added successfully!";
        } else {
            $_SESSION['message'] = "This user is already a contact.";
        }
    } else {
        $_SESSION['message'] = "No user found with that email.";
    }
    header("Location: home.php");
    exit();
}
?>
