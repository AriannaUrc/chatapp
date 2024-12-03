<!doctype html>
<html lang="en">
  <head>
    <title>Create new account</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- <link href="https://fonts.googleapis.com/css?family=Roboto|Courgette|Pacifico:400,700" rel="stylesheet"> CANT FIND-->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body>
      
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="css/signup.css">

    <div class="signup-form">
        <form action="" method="post">
            <div class="form-header">
                <h2>Sign up</h2>
                <p>Fill out this form and start chatting with your friends.</p>
            </div>
            <div class="form-group">
                <label for="user_name">Username</label>
                <input type="text" class="form-control" name="user_name" placeholder="Alice" autocomplete="off" Required>
            </div>

            <div class="form-group">
                <label for="user_pass">Password</label>
                <input type="password" class="form-control" name="user_pass" placeholder="Password" autocomplete="off" Required>
            </div>
            

            <div class="form-group">
                <label for="user_email">Email</label>
                <input type="email" class="form-control" name="user_email" placeholder="someone@site.com" autocomplete="off" Required>
            </div>

            <div class="form-group">
                <label for="user_country">Country</label>
                <select class="form-control" name="user_country" id="" Required>
                    <option disabled="">Select your country</option>
                    <option value="au">Australia</option>
                    <option value="br">Brazil</option>
                    <option value="ca">Canada</option>
                    <option value="cn">China</option>
                    <option value="de">Germany</option>
                    <option value="fr">France</option>
                    <option value="gb">United Kingdom</option>
                    <option value="in">India</option>
                    <option value="it">Italy</option>
                    <option value="jp">Japan</option>
                    <option value="mx">Mexico</option>
                    <option value="za">South Africa</option>
                    <option value="us">United States</option>
                </select>
            </div>

            <div class="form-group">
                <label for="user_gender">Gender</label>
                <select class="form-control" name="user_gender" id="" Required>
                    <option disabled="">Select your gender</option>
                    <option value="f">Male</option>
                    <option value="m">Female</option>
                    <option value="o">Other</option>
                    <option value="n">Rather not disclose</option>
                </select>
            </div>

            <div class="form-group">
                <label class="checkbox-inline" for="policy">
                <input type="checkbox" name="policy" Required> I accept the <a href="#">Terms of Use</a> &amp; <a href="#">Privacy Policy</a></label>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block btn-lg" name="sign_up">Sign up</button>
            </div>
            <?php include("signup_user.php")?>
        </form>

        <div class="text-center small" style="color: #67428B;"> Already have an account? <a href="signin.php">Sign in</a></div>
    </div>
   </body>
</html>