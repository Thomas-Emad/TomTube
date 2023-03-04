<?php
include 'db.php';
include 'cont.php';

// urldecode Used For Arabic Search.
$search = urldecode(str_replace("+", '%', explode('=', $_SERVER['REQUEST_URI'])[1]));
if (!isset($search) || empty($search)) {
  header('Location: index.php');
  exit();
}


$que = mysqli_query($db, "SELECT v.url_video, v.name_video, u.img_profile, u.name_channle, v.`bg-img-video`, v.watch, v.time_add FROM `users` u INNER JOIN `videos` v ON (u.random_user = v.random_user) AND v.name_video LIKE '%$search%' ORDER BY v.time_add DESC;");
$rows = mysqli_fetch_all($que);

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
  <link rel="stylesheet" href="css/search.css">
  <title>TomTube | Search Page</title>
  <style></style>
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
  <!-- search -->
  <div class="search">
    <?php
    $video = 0;
    foreach ($rows as $row) {
      $video++;
      $time_create = date_create($row[6]);
      $time_formate = date_format($time_create, 'Y/m/d h-i');
      echo "<a href='video.php?v=$row[0]' class='video'>
        <div class='box'>
          <img src='img/bg_video/$row[4]' class='img_v' alt='img video'>
          <div class='info'>
            <div class='info_video'>
              <p class='title-v'>$row[1]</p>
              <small>($row[5] Views)</small>
              <small>$time_formate</small>
            </div>
            <div class='info_ch'>
              <img src='img/img_users/$row[2]' alt='img profile' class='img_profile'>
              <span>Tom</span>
            </div>
          </div>
        </div>
      </a>";
    }
    if ($video == 0) {
      echo "<div class='video text-center'>
        Don't Get Your Video.
      </div>";
    }
    ?>

  </div>

  <script src="js/bootstrap.bundle.min.js"></script>

</body>

</html>