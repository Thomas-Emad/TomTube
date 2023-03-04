<?php
include 'db.php';
include 'cont.php';

// If User Login Get Has Random Name.
if (isset($_SESSION['user']['random_user'])) {
  $username = $_SESSION['user']['random_user'];
}

// Check Form URL IS Want To Print All Videos??.
if (isset(explode('v=', $_SERVER['REQUEST_URI'])[1])) {
  $url = explode('v=', $_SERVER['REQUEST_URI'])[1];
} else {
  $url = '';
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/style.css">
  <title>TomTube | Home</title>
  <style>
  </style>
</head>

<body>
  <?php
  // Change Mode Site From Cookies 
  if ($mode == 'dark') {
    echo "<script>$mode_dark</script>";
  } elseif ($mode == 'light') {
    echo "<script>$mode_light</script>";
  }
  ?>
  <header>
    <?php echo $header; ?>
  </header>
  <!-- Content -->
  <div class="content">
    <?php
    // If Don't Have User Or Want To Print All Videos.
    if (!isset($username) || $url == '*') {
      $que = mysqli_query($db, "SELECT u.random_user, u.name_channle, u.img_profile, v.url_video ,v.name_video, v.`bg-img-video`, v.time_add, v.watch FROM `users` u INNER JOIN `videos` v ON (u.random_user = v.random_user) ORDER BY v.time_add DESC;");
      $rows = mysqli_fetch_all($que);
      foreach ($rows as $row) {
        $time_create = date_create($row[6]);
        $time_formate = date_format($time_create, 'Y/m/d h-i');
        echo "
          <div class='box'>
            <a href='video.php?v=$row[3]'><img src='img/bg_video/$row[5]' alt='img video' class='img-video'></a>
            <div class='info'>
              <a  href='#' class='title'>$row[4]</a>
              <div class='info_ch'>
                <a href='profile.php?u=$row[0]'><img src='img/img_users/$row[2]' alt='Icon Profile'></a>
                <span class='title_ch'>$row[1]<span class='text_color'>($row[7] Views)  $time_formate</span>
              </div>
            </div>
          </div>";
      }
    } elseif (isset($username)) {
      // Get Followers From Table User, Then Transformation To Array
      $que = mysqli_query($db, "SELECT follow FROM `users` WHERE random_user = '$username'");
      $user_follows_as_string = mysqli_fetch_row($que)[0];
      $user_follows_as_array = explode(',', $user_follows_as_string);
      $video = 0;
      // Print All Videos The User Was Follow.
      foreach ($user_follows_as_array as $u_f) {
        $que = mysqli_query($db, "SELECT u.random_user, u.name_channle, u.img_profile, v.url_video ,v.name_video, v.`bg-img-video`, v.time_add, v.watch FROM `users` u INNER JOIN `videos` v ON (u.random_user = v.random_user) AND u.random_user = $u_f ORDER BY v.time_add DESC;");
        $rows = mysqli_fetch_all($que);
        foreach ($rows as $row) {
          $time_create = date_create($row[6]);
          $time_formate = date_format($time_create, 'Y/m/d h-i');
          ++$video;
          echo "
            <div class='box'>
              <a href='video.php?v=$row[3]'><img src='img/bg_video/$row[5]' alt='img video' class='img-video'></a>
              <div class='info'>
                <a  href='#' class='title'>$row[4]</a>
                <div class='info_ch'>
                  <a href='profile.php?u=$row[0]'><img src='img/img_users/$row[2]' alt='Icon Profile'></a>
                  <span class='title_ch'>$row[1]<span class='text_color'>($row[7] Views) $time_formate</span>
                </div>
              </div>
            </div>";
        }
      }

      // IF User Don't Have Any Video In His Follows, Print All.
      if ($video == 0) {
        $que = mysqli_query($db, "SELECT u.random_user, u.name_channle, u.img_profile, v.url_video ,v.name_video, v.`bg-img-video`, v.time_add, v.watch FROM `users` u INNER JOIN `videos` v ON (u.random_user = v.random_user) ORDER BY v.time_add DESC;");
        $rows = mysqli_fetch_all($que);
        foreach ($rows as $row) {
          $time_create = date_create($row[6]);
          $time_formate = date_format($time_create, 'Y/m/d h-i');
          echo "
            <div class='box'>
              <a href='video.php?v=$row[3]'><img src='img/bg_video/$row[5]' alt='img video' class='img-video'></a>
              <div class='info'>
                <a  href='#' class='title'>$row[4]</a>
                <div class='info_ch'>
                  <a href='profile.php?u=$row[0]'><img src='img/img_users/$row[2]' alt='Icon Profile'></a>
                  <span class='title_ch'>$row[1]<span class='text_color'>($row[7] Views) / $time_formate</span>
                </div>
              </div>
            </div>";
        }
      }
    }
    ?>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>

</body>

</html>