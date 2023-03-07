<?php
include 'db.php';
include 'cont.php';

// Anwser Errors Header.
ob_start();

// User name Watch.
if (isset($_SESSION['user']['random_user'])) {
  $username = $_SESSION['user']['random_user'];
} else {
  $username = '';
}

// Get Video By Url
$url_video = explode('=', $_SERVER['REQUEST_URI'])[1];
if (!isset($url_video) || empty($url_video)) {
  header('Location: index.php');
  exit();
}

// SQL This Video.
$que = mysqli_query($db, "SELECT v.url_video, v.name_video, v.video, u.random_user, u.name_channle, u.img_profile, v.time_add, v.dis, v.watch FROM `users` u INNER JOIN `videos` v ON (u.random_user = v.random_user) AND v.url_video = '$url_video';");
$row_video = mysqli_fetch_all($que)[0];

// If You Can't Get Page Transformation To 404 Page.
if (empty($row_video[0])) {
  header('Location: 404.php');
  exit();
}

// First You Open Video, +1 Watch.
$watchs = $row_video[8] + 1;
$que = mysqli_query($db, "UPDATE `videos` SET `watch` = '$watchs' WHERE url_video = '$url_video';");

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
  <link rel="stylesheet" href="css/video.css">
  <title>TomTube | <?php echo $row_video[4] . ' | ' . $row_video[1] ?></title>
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
  <!-- Content -->
  <div class="content_v">
    <div class="watch">
      <?php
      // Get Video.
      echo "
      <div class='vid'>
        <video src='videos/$row_video[2]' class='v_w' controls autoplay></video>
        <div class='info'>
          <div class='vid_info'>
            <span class='name_vid'>$row_video[1]</span>
            <div class='data text_color'>
              <span>($row_video[8] Views)</span>
              <span>$row_video[6]</span>
            </div>
          </div>
          <a href='profile.php?u=$row_video[3]'  class='ch'> 
            <span>$row_video[4]</span>
            <img src='img/img_users/$row_video[5]' class='img_profile' alt='Icon Channle'>
          </a>
        </div>
      </div>
      <div class='dis'>$row_video[7]</div>";
      ?>
      <!-- Commits -->
      <div class="comm mt-2">
        <h2 class="title">Commit</h2>
        <form action="" method="POST">
          <input type="text" name="commit" placeholder="Add Your Commit." required>
          <input type="submit" name="add_commit" value="Send">
        </form>
        <?php
        // Get All Commits, And Sort It.
        $sql = mysqli_query($db, "SELECT comm FROM `videos` v WHERE `url_video` =  '$url_video';");
        $reslut = mysqli_fetch_row($sql)[0];
        $big = array_reverse(explode('nucm=?', $reslut));

        echo '<form action="" method="POST" class="row">';
        for ($i = 0; $i < (sizeof($big) - 1); $i++) {
          // Tansformtion commits To Array, For All Commits.
          $commit = explode('cm=?', $big[$i]);
          $sql_commit = mysqli_query($db, "SELECT name_channle, img_profile FROM `users`  WHERE `random_user` =  '$commit[0]';");
          $info_user = mysqli_fetch_row($sql_commit);

          // That's For The Commit Is Not Exist, Don't Print It.
          if ($sql_commit->num_rows !== 0) {
            echo "
              <div class='box'>
                <div class='info_commit'>
                  <a href='profile.php?u=$commit[0]'><img src='img/img_users/$info_user[1]' alt=''></a>
                  <div class='mess'>
                    <span class='name_channle'>$info_user[0]</span>
                    <p>$commit[1]</p>
                  </div>
                </div>";
            if ($username == $commit[0] || $username == $row_video[3]) {
              echo "
                  <div class='del_commit'>
                  <input type='submit' name='del_commit' value='$i' id='$i' class='input_del'>
                  <label for='$i' class='lable_del'>Delete</label>
                  </div>
                  ";
            }
            echo "</div>";
          }
        }
        echo '</form>';
        // Send Your Commit.
        date_default_timezone_set('Africa/Cairo');
        if (isset($_POST['add_commit'])) {

          if (isset($username) && $username != '') {
            $commit = str_replace("'", "", filter_var($_POST['commit'], FILTER_SANITIZE_EMAIL));
            $add_commit = $reslut  . 'nucm=?' . $username . 'cm=?' . $commit . '<br><span class="time">' . date('Y/m/d h-i') . '</span>';
            mysqli_query($db, "UPDATE videos SET comm = '$add_commit' WHERE url_video = '$url_video';");
            header("Refresh: 0");
          } else {
            echo '<script>window.alert("Can\'t Send Your Message, You Need To Login First.")</script>';
          }
        }

        // When You Click Delete Commit, Will Get Number in Array And Unset Than Send It. 
        if (isset($_POST['del_commit'])) {
          $commit_delete = $_POST['del_commit'];
          unset($big[$commit_delete]);
          $new_commit_as_string = implode('nucm=?', $big);
          mysqli_query($db, "UPDATE `videos` SET comm = '$new_commit_as_string' WHERE url_video = '$url_video';");
          header("Refresh: 0");
        }

        ?>

      </div>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>

</body>

</html>