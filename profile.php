<?php
include 'db.php';
include 'cont.php';

// Get User Form URL
$name_user = explode('=', $_SERVER['REQUEST_URI'])[1];

// IF User Login, Get His Number.
if (isset($_SESSION['user']['random_user'])) {
  $username = $_SESSION['user']['random_user'];
  // Check From Status.
  if ($username == $name_user) {
    $que = mysqli_query($db, "SELECT email, status FROM `users` WHERE `random_user` = '$username';");
    $real_status = explode(',', mysqli_fetch_row($que)[1]);
    if ($real_status[0] == 'not active') {
      echo '
      <div style="background-color: #222;width: 100%;height: 100vh;z-index: 1000000;position: absolute;top: 0;left: 0;">
        <div style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%); text-align: center">
          <div style="font-size:1.3rem;">Please, Activate Your Account</div>
          <small>(Check From Your Email)</small>
          <form action="" method="POST" class="w-100 mt-3">
          <input type="submit" name="logout" value="Logout" class="btn btn-outline-warning w-100">
          </form>
        </div>
      </div>';
    }
  }
} else {
  $username = '';
}



// Get All Information About My User.
$que = mysqli_query($db, "SELECT u.email, u.password, u.name_channle, u.img_profile, u.time_create, COUNT(v.`url_video`) FROM `users` u INNER JOIN `videos` v ON (u.random_user = v.random_user) AND u.random_user = '$name_user';;");
$row = mysqli_fetch_row($que);
$email = $row[0];
$password = $row[1];
$name_channle = $row[2];
$img_profile = $row[3];
$time_create = $row[4];
$nums_videos = $row[5];

// If You Can't Get Page Transformation To 404 Page.
if (empty($row[0])) {
  header('Location: 404.php');
  exit();
}

// Send All Change In Information
if (isset($_POST['send_change'])) {
  // check Form Type Img Profile.
  $types_img = ['png', 'jpg', 'jpeg'];
  if (!empty($_FILES['img_profile']['type'])) {
    if (!in_array(explode('/', $_FILES['img_profile']['type'])[1], $types_img)) {
      $errors[] = "You Can't Upload This Image Because Has Type.";
    }
  }

  $new_name = $_POST['new_name_channle'];
  $new_password = $_POST['new_password'];
  // If Don't Have Any Error Send New Channle.
  if (empty($errors)) {
    if (empty($_FILES['img_profile']['type'])) {
      $que = mysqli_query($db, "UPDATE `users` SET `name_channle` = '$new_name', `password` = '$new_password' WHERE `random_user` = '$username'");
    } else {
      $que = mysqli_query($db, "UPDATE `users` SET `name_channle` = '$new_name', `password` = '$new_password', `img_profile` = '$username.png' WHERE `random_user` = '$username'");
      move_uploaded_file($_FILES['img_profile']['tmp_name'], 'img/img_users/' . $username  . '.png');
    }
    header('Refresh:0');
  } else {
    echo "<script>window.alert('Can\'t Change Any Thing!');</script>";
    header("Refresh: 0");
  }
}

// LogOut User
if (isset($_POST['logout'])) {
  session_unset();
  header('Location: index.php');
}

// Upload Video
if (isset($_POST['upload_video'])) {
  $type_allow_img =  ['png', 'jpg', 'jpeg', 'webp'];
  $type_allow_video =  ['mp4', 'm4p', 'm4v'];

  $title_video = $_POST['title_video'];
  $dec_video = $_POST['dec_video'];
  $url_video_random = rand(5000, 1000000);

  if (strlen($title_video) == 0) {
    $errors[] = "Cant Upload Empty (Title)";
  }

  // Get Type Video, And Change Has Name
  if ($_FILES['video']['error'] != 4) {
    $type_video = explode('/', $_FILES['video']['type'])[1];
    if (!in_array($type_video, $type_allow_video)) {
      $errors[] = "Cant Upload (Video), Because Has Type: $type_video";
    }
  } else {
    $errors[] = "Cant Upload Empty (Video)";
  }

  // Get Type Img, And Change Has Name
  if ($_FILES['bg_video']['error'] != 4) {
    $type_img = explode('/', $_FILES['bg_video']['type'])[1];
    if (!in_array($type_img, $type_allow_img)) {
      $errors[] = "Cant Upload (Image), Because Has Type: $type_img";
    }
  } else {
    $errors[] = "Cant Upload Empty (Image)";
  }
  // IF Don't Have Any Errors Upload Video.
  if (empty($errors)) {
    // Upload Background And Video.
    move_uploaded_file($_FILES['bg_video']['tmp_name'], 'img/bg_video/' . $url_video_random . '.' . $type_img);
    move_uploaded_file($_FILES['video']['tmp_name'], 'videos/' . $url_video_random . '.' . $type_video);

    $sql = "INSERT INTO `videos` (`id`, `random_user`, `name_video`, `video`, `bg-img-video`, `time_add`, `url_video`, `dis`) 
    VALUES (NULL, '$username', '$title_video', '$url_video_random.$type_video', '$url_video_random.$type_img', current_timestamp(), '$url_video_random', '$dec_video');";
    mysqli_query($db, $sql);

    header("Refresh: 0");
  } else {
    echo "<script>window.alert('";
    foreach ($errors as $erro) {
      echo "$erro/";
    }
    echo "');</script>";
    header("Refresh: 0");
  }
}

// Get All My Follow, Then Transformtion String To Array.
if ($username !== '') {
  $que = mysqli_query($db, "SELECT follow, followers FROM `users` WHERE random_user = '$username'");
  $sql = mysqli_fetch_row($que);
  $user_follows_as_string = $sql[0];
  $user_follows_as_array = explode(',', $user_follows_as_string);
}

// Know Count Followers This User.
$que = mysqli_query($db, "SELECT follow, followers FROM `users` WHERE random_user = '$name_user'");
$sql = mysqli_fetch_row($que);
$followers_count = $sql[1];

## Event Subscribe.
if (isset($_POST['sub'])) {
  // If User Not Subscribe Do That, Else Unsubscribe His User.
  if (!array_search($name_user, $user_follows_as_array)) {
    mysqli_query($db, "UPDATE `users` SET `follow` = '$user_follows_as_string,$name_user' WHERE random_user = '$username';");
    mysqli_query($db, "UPDATE `users` SET `followers` = $followers_count + 1 WHERE random_user = '$name_user';");
  } else {
    // Delete User Than Transformtion To String, And -1 From Followers This User.
    unset($user_follows_as_array[array_search($name_user, $user_follows_as_array)]);
    $new_follows_as_string = implode(',', $user_follows_as_array);
    mysqli_query($db, "UPDATE `users` SET `follow` = '$new_follows_as_string' WHERE random_user = '$username';");
    mysqli_query($db, "UPDATE `users` SET `followers` = $followers_count - 1 WHERE random_user = '$name_user';");
  }
  header('Refresh: 0');
}

// Get All Videos, From This User.
$que = mysqli_query($db, "SELECT u.random_user, u.name_channle, u.img_profile, v.name_video, v.url_video, v.`bg-img-video`, v.time_add, v.watch, v.video FROM `users` u JOIN `videos` v ON (u.`random_user` = v.random_user) AND (u.`random_user` = $name_user) ORDER BY v.time_add DESC;");
$my_videos = mysqli_fetch_all($que);

// Delete Account
if (isset($_POST['del'])) {
  unlink("img/bg_video/$img_profile");
  foreach ($my_videos as $video) {
    unlink("img/bg_video/$video[5]");
    unlink("videos/$video[8]");
  }
  $que = mysqli_query($db, "DELETE FROM `users` WHERE `random_user` = '$username'");
  session_unset();
  header('Location: index.php');
}

// Delete Video
if (isset($_POST['del_video'])) {
  // When You Delete Video Will Delete With It His Background And Video.
  $del_video = $_POST['del_video'];
  mysqli_query($db, "DELETE FROM videos WHERE `videos`.`url_video` = $del_video");
  foreach (scandir('img/bg_video/') as $file) {
    if (!empty(stristr($file, "$del_video"))) {
      unlink("img/bg_video/" . stristr($file, "$del_video"));
      break;
    }
  }
  foreach (scandir('videos/') as $file) {
    if (!empty(stristr($file, "$del_video"))) {
      unlink("videos/" . stristr($file, "$del_video"));
      break;
    }
  }
  header('Refresh: 0');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/profile.css">
  <title>TomTube | <?php echo $name_channle ?></title>
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
  <!-- Start Content Info profile -->
  <div class="content_pro">
    <div class="information f-j-c">
      <div class="info f-j-c">
        <img src="img/img_users/<?php echo $img_profile; ?>" class="img-profile" alt="">
        <div class="text f-j-c">
          <span>Name Channle: <?php echo $name_channle ?></span>
          <span>nums Videos: <?php echo $nums_videos ?></span>
          <span>Folows: <?php echo $followers_count; ?></span>
        </div>
      </div>
      <?php
      if ($username == $name_user) {
        echo '
        <div class="changes f-j-c">
          <div class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#change_info">Change</div>
          <form action="" method="POST" class="w-100">
          <input type="submit" name="logout" value="Logout" class="btn btn-outline-warning w-100">
          </form>
          <div class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#add_video">Add Video</div>
        </div>';
      } else {
        if ($username == '') {
          echo '
          <div class="changes f-j-c">
            <form action="account.php?site=login" method="POST" class="w-100">
              <input type="submit" name="sub" value="Subscribe" class="btn btn-success w-100">
            </form>
          </div>';
        } else {
          if (in_array($name_user, $user_follows_as_array)) {
            echo '
            <div class="changes f-j-c">
              <form action="" method="POST" class="w-100">
                <input type="submit" name="sub" value="Unsubscribe" class="btn btn-outline-warning w-100">
              </form>
            </div>';
          } else {
            echo '
            <div class="changes f-j-c">
              <form action="" method="POST" class="w-100">
                <input type="submit" name="sub" value="Subscribe" class="btn btn-success w-100">
              </form>
            </div>';
          }
        }
      }
      ?>
    </div>
  </div>
  <?php
  // If You My User Echo Edit, And Upload Videos, Delete Video, Delete Account
  if ($username == $name_user) {
    echo "
    <!-- Modal ==> Change Info Channle -->
    <div class='modal fade text-dark' id='change_info' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
      <div class='modal-dialog modal-fullscreen'>
        <div class='modal-content '>
          <div class='modal-header'>
            <h1 class='modal-title fs-5' id='staticBackdropLabel'>Change Your Information</h1>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <form action='' method='POST' enctype='multipart/form-data'>
              <div class='input-group mb-2'>
                <input type='file' class='form-control' id='img_profile' name='img_profile'>
                <label class='input-group-text' for='img_profile'>Icon Channle</label>
              </div>
              <div class='form-floating mb-2'>
                <input type='text' class='form-control' id='channle_name' name='new_name_channle' placeholder='Channle Name' value='$name_channle'>
                <label for='channle_name'>Channle Name</label>
              </div>
              <div class='form-floating mb-2'>
                <input type='email' class='form-control' id='email_input' placeholder='Email address' value='$email' disabled>
                <label for='email_input'>Email address</label>
              </div>
              <div class='form-floating mb-2'>
                <input type='password' class='form-control' id='password_input' name='new_password' value='$password' placeholder='Password'>
                <label for='password_input'>Password</label>
              </div>
              <div class='form-floating mb-2'>
                <input type='text' class='form-control' id='time_create_acc' placeholder='Time Create' value='$time_create' disabled>
                <label for='time_create_acc'>Time Create</label>
              </div>
              <div class='btn btn-outline-danger' data-bs-toggle='modal' data-bs-target='#delet_account'>Delete Account</div>
              <!-- Your Videos -->
              <div class='videos'>
              <h3 class='title'>Your Videos</h3>
              ";
    // Get Date Video And Print it.
    // $que = mysqli_query($db, "SELECT u.random_user, u.name_channle, u.img_profile, v.name_video, v.url_video, v.`bg-img-video`, v.time_add, v.watch FROM `users` u JOIN `videos` v ON (u.`random_user` = v.random_user) AND (u.`random_user` = $username) ORDER BY v.time_add DESC;");
    // $info_video = mysqli_fetch_all($que);
    foreach ($my_videos as $video) {
      echo "
            <div class='box'>
              <img src='img/bg_video/$video[5]'>
              <div class='info'>
                <div class='info_video'>
                  <p>Title Video: $video[3]</p>
                  <span class='title_ch'><span class='text_color'>($video[7] Views) $video[6]</span>
                </div>
                <div class='inputs'>
                  <input type='submit' name='del_video' value='$video[4]' id='del$video[4]' class='hidd_input'>
                  <label for='del$video[4]'>Delete</label>
                </div>
              </div>
            </div>
            ";
    }
    if (empty($my_videos)) {
      echo '<div class="no_thing">You Don\'t Have Any Videos.</div>';
    }
    echo "
              </div>
          </div>
          <div class='modal-footer'>
            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Don't Save</button>
            <input type='submit' name='send_change' value='Save Change' class='btn btn-success'>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Modal ==> Delete Account -->
    <div class='modal fade text-dark' id='delet_account' tabindex='-1' aria-hidden='true'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-header'>
            <h1 class='modal-title fs-5'>Delete Your Account?</h1>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            Are You Want To Delete Your Account?!!
          </div>
          <div class='modal-footer'>
            <form action='' method='POST'>
              <button type='button' class='btn btn-success' data-bs-dismiss='modal'>Close</button>
              <input type='submit' name='del' class='btn btn-outline-danger' value='Delete'>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal ==> Add Video -->
    <div class='modal fade text-dark' id='add_video' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
      <div class='modal-dialog modal-fullscreen'>
        <div class='modal-content '>
          <div class='modal-header'>
            <h1 class='modal-title fs-5' id='staticBackdropLabel'>Add New Video</h1>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <form action='' method='POST' enctype='multipart/form-data'>
              <div class='input-group mb-2'>
                <input type='file' class='form-control' id='bg_video' name='bg_video' required>
                <label class='input-group-text' for='bg_video'>Background Video</label>
              </div>
              <div class='input-group mb-2'>
                <input type='file' class='form-control' id='video' name='video' required>
                <label class='input-group-text' for='video'>Your Video</label>
              </div>
              <div class='form-floating mb-2'>
                <input type='text' class='form-control' id='title_video' name='title_video' placeholder='Title Video' required>
                <label for='title_video'>Title Video</label>
              </div>
              <div class='form-floating mb-2'>
                <input type='text' class='form-control' id='dis' name='dec_video' placeholder='Description' value='No Thing!!'>
                <label for='dis'>Description</label>
              </div>
              <div style='text-align:center;'>
                <span>Wait A Minute Time, For Upload Your Video.</span><br>
                <small>(Don't, Close Site Now.)</small>
              </div>
          </div>
          <div class='modal-footer'>
            <button type='button' class='btn btn-outline-warning ps-4 pe-4' data-bs-dismiss='modal'>Close</button>
            <input type='submit' name='upload_video' value='Upload' class='btn btn-outline-success ps-4 pe-4'>
            </div>
          </form>
        </div>
      </div>
    </div>
    ";
  }
  ?>
  <!-- Start Content My Videos -->
  <div class="content_pro mt-1 content">
    <h2 class="title_section text-center ">Your Videos</h2>
    <?php
    if ($nums_videos > 0) {
      echo '<div class="grid-box">';
      foreach ($my_videos as $row) {
        $time_create = date_create($row[6]);
        $time_formate = date_format($time_create, 'Y/m/d h-i');
        echo "
            <div class='box'>
              <a href='video.php?v=$row[4]'><img src='img/bg_video/$row[5]' alt='img video' class='img-video'></a>
              <div class='info'>
                <a  href='#' class='title'>$row[3]</a>
                <div class='info_ch'>
                  <a href='profile.php?u=$row[0]'><img src='img/img_users/$row[2]' alt='Icon Profile'></a>
                  <span class='title_ch'>$row[1]<span class='text_color'>($row[7] Views)  $time_formate</span>
                </div>
              </div>
            </div>";
      }
    } else {
      echo '<div>';
      echo "<div class='no_thing'>You Don't Upload Any Videos.</div>";
    }
    echo '</div>';

    ?>

  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/all.min.js"></script>
</body>

</html>