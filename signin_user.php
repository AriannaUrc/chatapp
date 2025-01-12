<?php
session_start();

include("include/connection.php");

if(isset($_POST['sign_in'])){
    $email = htmlentities(mysqli_real_escape_string($con, $_POST['email']));
    $pass = htmlentities(mysqli_real_escape_string($con, $_POST['pass']));
    
    $select_user = "SELECT * from users where user_email ='$email'";
    $query = mysqli_query($con, $select_user);
    $check_user = mysqli_num_rows($query);

    if($check_user == 1){
        $row = mysqli_fetch_array($query);
        $stored_pass = $row['user_pass']; // Get the stored hashed password

        // Verify the entered password with the stored hash
        if(password_verify($pass, $stored_pass)){
            $_SESSION['user_email'] = $email;
            
            $update_msg = mysqli_query($con, "UPDATE users SET log_in = 'Online' WHERE user_email ='$email'");

            $user = $_SESSION['user_email'];

            $get_user = "SELECT * from users where user_email = '$user'";
            $run_user = mysqli_query($con, $get_user);
            $row = mysqli_fetch_array($run_user);

            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['user_id'] = $row['user_id'];
            
            echo "<script>window.open('home.php', '_self')</script>";
        } else {
            ?>
            <div class="alert alert-danger">
                <strong>Check your email and password.</strong>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="alert alert-danger">
            <strong>User not found.</strong>
        </div>
        <?php
    }
}
?>
