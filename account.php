<?php
include_once('db.php');

session_start();
if (isset($_SESSION['user'])) {
  header("Location: profile.php?u=" . $_SESSION['user']);
  exit();
}

// Get site By Url.
$url_site = strtolower(explode('=', $_SERVER['REQUEST_URI'])[1]);
if (!isset($url_site) || empty($url_site) || !in_array($url_site, ['login', 'register', 'forget_pass'])) {
  header('Location: 404.php');
  exit();
}

if ($url_site == 'login') {
  if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $errors = [];

    if (empty($email)) {
      $errors[] = "You Need Write Email";
    }
    if (empty($errors)) {
      $stm = "SELECT * FROM users WHERE email ='$email' AND password='$password'";
      $res = mysqli_query($db, $stm);
      $row = mysqli_fetch_assoc($res);

      if (isset($row)) {
        $_SESSION['user'] = ['random_user' => $row['random_user']];
        $random_user = $_SESSION['user']['random_user'];
        header("Location: profile.php?u=$random_user");
        exit();
      } else {
        $errors[] = "Failed In Login";
      }
    }
  }
} elseif ($url_site == 'register') {
  if (isset($_POST['submit'])) {
    $name_channle = str_replace("'", "", filter_var($_POST['name_channle'], FILTER_SANITIZE_EMAIL));
    $email =  filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password =  filter_var($_POST['password'], FILTER_SANITIZE_EMAIL);
    $time_create = $_POST['time_create'];

    $errors = [];

    // Check From Name, Email, Password
    if (strlen($name_channle) < 3) {
      $errors[] = "You Need Write > 3 Char In Username";
    }
    if (empty($email)) {
      $errors[] = "You Need Write Email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "You Need Write Good Email";
    }
    if (strlen($password) < 6) {
      $errors[] = "You Need Write Password >= 6 chars";
    } elseif (strlen($password) > 12) {
      $errors[] = "You Need Write Password <= 12 chars";
    }

    $random_user = rand(1, 1000000);
    // IF You Don't Upload Img Profile Choose One.
    if ($_FILES['img_profile']['error'] == 4) {
      $img_profile = 'someone.png';
    } else {
      // Check For Type Img Profile
      $types_img = ['png', 'jpg', 'jpeg', ''];
      if (!in_array(explode('/', $_FILES['img_profile']['type'])[1], $types_img)) {
        $errors[] = "You Can't Upload This Image Because Has Type.";
      }
      $img_profile = $random_user . '.png';
    }

    if (empty($errors)) {
      $stm = "SELECT * FROM users WHERE email ='$email'";
      $res = mysqli_query($db, $stm);
      $rows = mysqli_num_rows($res);
      if ($rows === 1) {
        $errors[] = "You Need Write Other Email";
      } else {
        $random_num_active_email = rand(0, 100000);
        $_SESSION['user'] = ['random_user' => $random_user];
        $stm = "INSERT INTO `users` (`id`, `random_user`, `email`, `password`, `name_channle`, `img_profile`, `follow`, `followers`, `time_create`, `status`) VALUES
                (NULL, '$random_user', '$email', '$password', '$name_channle', '$img_profile', '$random_user', 0, current_timestamp(), 'not active,$random_num_active_email');";
        $res = mysqli_query($db, $stm);
        move_uploaded_file($_FILES['img_profile']['tmp_name'], 'img/img_users/' . $img_profile);

        // For Message Acitve Email.
        date_default_timezone_set("Africa/Cairo");
        $date = date_format(date_create(), 'Y/m/d h-i');
        include('phpmailer.php');
        $mail->Subject = "Hello $name_channle, Active Your Email.";
        $mail->Body = "
        <div style='background-color: #222; font-size: 1.2rem; text-align: left; padding: 15px; color: #fff;'>
          <h2 style='text-align:center; font-size:2rem'>TomToube</h2>
          <h3 style='font-size:1.4rem'>Active Your Account</h3>
          <div style='padding: 5px 0; font-size:1rem;'>
            <div style='color: #fff;'>Your Name: $name_channle</div>
            <div style='color: #fff;'>Your Email: $email</div>
            <div style='color: #fff;'>Time Create: $date</div>
          </div>
          <a href='http://thomas-emad.ml/projects/TomTube/active_email.php?e=$email&vc=$random_num_active_email' style='color: #222; text-decoration: none; padding: 10px 15px; background-color: #fff; display: block; margin: 10px 0;  text-align: center;'>Acitve</a>
        </div>";
        $mail->addAddress("$email");
        $mail->send();

        header("Location: profile.php?u=$random_user");
        exit();
      }
    }
  }
} elseif ($url_site == 'forget_pass') {
  if (isset($_POST['forget_pass'])) {
    $email = $_POST['email'];
    $que = mysqli_query($db, "SELECT name_channle, email, password, time_create FROM `users` WHERE email = '$email'");
    $row = mysqli_fetch_row($que);

    // Message For Send Password In Email.
    include('phpmailer.php');
    $mail->Subject = "Hello $row[0], Active Your Email.";
    $mail->Body = "
          <div style='background-color: #222; font-size: 1.2rem; text-align: left; padding: 15px; color:#fff;'>
            <h2 style='text-align:center; font-size:2rem'>TomToube</h2>
            <h3 style='font-size:1.4rem'>Forgot Password</h3>
            <div style='padding: 5px 0; font-size:1rem;'>
              <div style='color:#fff;'>Your Name: $row[0]</div>
              <div style='color:#fff;'>Your Email: $row[1]</div>
              <div style='color:#fff;'>Your Password: $row[2]</div>
              <div style='color:#fff;'>Time Create: $row[3]</div>
            </div>
          </div>";
    $mail->addAddress("$email");
    $mail->send();
    header('Location: account.php?site=login');
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TomTube || <?php echo ucfirst($url_site); ?></title>
  <link rel="stylesheet" href="css/account.css">
</head>

<body>

  <div class="parent">
    <?php
    // Print Site Use URL.
    if ($url_site == 'login') {
      echo '    
      <form action="" method="POST">
        <h2 class="title">Login</h2>
        <input type="email" name="email" placeholder="Your Email" value="';
      if (isset($_POST["email"])) echo $_POST["email"];
      echo '">';
      echo '
          <input type="password" name="password" placeholder="Your Password">
          <input type="submit" name="submit" value="Send">
          <a href="account.php?site=register" class="trans_page">New Account</a>
          <a href="account.php?site=forget_pass" class="trans_page_text">Forgot Your Password?!</a>
        </form>
          ';
      if (isset($errors)) {
        foreach ($errors as $err) {
          echo "<p class='error'>" . $err . "</p>";
        }
      }
      exit();
    } elseif ($url_site == 'register') {
      echo '
      <form action="" method="POST" enctype="multipart/form-data">
          <h2 class="title">New Account</h2>
          <input type="text" name="name_channle" placeholder="Name Channle" value="';
      if (isset($_POST['name_channle'])) echo $_POST['name_channle'];
      echo '">';
      echo '
          <input type="text" name="email" placeholder="Your Email" value="';
      if (isset($_POST['email'])) echo $_POST['email'];
      echo '">';
      echo '
          <input type="password" name="password" placeholder="Your Password">
          <input type="file" name="img_profile">
          <input type="hidden" name="time_create" value="';
      echo date("Y-m-d H:i:s") . '">';
      echo '
          <input type="submit" name="submit" value="Send">
          <a href="account.php?site=login" class="trans_page">Login</a>
      </form>';


      if (isset($errors)) {
        foreach ($errors as $err) {
          echo "<p class='error'>" . $err . "</p>";
        }
      }
      exit();
    } elseif ($url_site == 'forget_pass') {
      echo '
      <div class="parent">
        <form action="" method="POST">
          <h2 style="margin:0;">Forget Your Password?!</h2>
          <input type="email" name="email" placeholder="Write Your Email...">
          <input type="submit" name="forget_pass" value="Send">
          <small>(Will Send Your Password In Your Email)</small>
        </form>
        <a href="account.php?site=login" class="trans_page_text">Login</a><br>
        <a href="account.php?site=register" class="trans_page_text">New Account</a>
      </div>
      ';
      exit();
    }
    ?>

  </div>
</body>

</html>