<!doctype html>
<html lang="en">
  <head>
    <title>My Chat - HOME</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body>
      
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  
    <div class="container main-section">
        <div class="row">
            <div class="col-md-3 col-sm-3 col-xs-12 left-sidebar">

                <div class="input-group searchbox">
                    <div class="input-group-btn">
                        <center><a href="inlcude/find_friends.php"><button class="btn btn-default search-icon" name="search_user" type="submit">Add new user</button></a></center>
                    </div>
                </div>

                <div class="left-chat">
                    <?php include("include/get_users.data.php"); ?>
                </div>
            </div>

            <div class="col-md-9 col-sm-9 col-xs-12 right-sidebar">
                <div class="row">
                    <!--Get the infos of the logged in user -->
                    <?php 
                        $user = $_SESSION["user_email"];
                        $get_user = "select * from users where user_email = $user";
                        $run_user = mysqli_query($conn, $get_user);
                        $row = mysqli_fetch_array( $run_user );

                        $user_id = $row['user_id'];
                        $user_name = $row['user_name'];
                    ?>

                    <!--getting data of the clicked user -->
                    <?php 
                        if(isset($_GET['user_name'])){
                            global $conn;

                            $get_username = $_GET['user_name'];
                            $get_user = "select * from users where users_name = $get_username";
                            $run_user = mysqli_query($conn, $get_user);
                            $row_user = mysqli_fetch_array( $run_user );

                            $username = $row_user["user_name"];
                            $user_profile_image = $row_user["user_profile"];
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>


    </body>
</html>