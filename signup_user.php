<?php 
include("include/connection.php");

if(isset($_POST['sign_up'])){
    $name = htmlentities(mysqli_real_escape_string($con, $_POST['user_name']));
    $pass = htmlentities(mysqli_real_escape_string($con, $_POST['user_pass']));
    $email = htmlentities(mysqli_real_escape_string($con, $_POST['user_email']));
    $country = htmlentities(mysqli_real_escape_string($con, $_POST['user_country']));
    $gender = htmlentities(mysqli_real_escape_string($con, $_POST['user_gender']));
    $rand = rand(1,3);

    if($name == ''){
        echo "<script>alert('We couldn't verify your name')</script>";
        exit();
    }
    if(strlen($pass) < 8){
        echo "<script>alert('The password should be at least 8 characters!')</script>";
        exit();
    }
    if (strpos($name, ' ') !== false) {
        echo "<script>alert('Username should not contain spaces!')</script>";
        exit();
    }

    // Fix the email check query
    $check_email = "select * from users where user_email = '$email'";
    $run_email = mysqli_query($con, $check_email);
    $check = mysqli_num_rows($run_email);
    if($check == 1){
        echo "<script>alert('This email has already been taken, please try again!')</script>";
        echo "<script>window.open('signup.php', '_self')</script>";
        exit();
    }

    // Hash the password before storing
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

    // Fix the profile picture assignment
    if($rand == 1)
        $profile_pic = "images/pfp1.jpeg";
    elseif($rand == 2)
        $profile_pic = "images/pfp2.jpeg";
    elseif($rand == 3)
        $profile_pic = "images/pfp3.jpeg";

    // Fix the insert query
    $insert = "insert into users (user_name, user_pass, user_email, user_profile, user_country, user_gender) values('$name', '$hashed_pass', '$email', '$profile_pic', '$country', '$gender')";
    
    $query = mysqli_query($con, $insert);
    if($query){
        echo "<script>alert('Congrats $name, your account has been created!')</script>";
        echo "<script>window.open('signin.php', '_self')</script>";
    } else {
        echo "<script>alert('Registration failed, try again')</script>";
        echo "<script>window.open('signup.php', '_self')</script>";
    }
}
?>
