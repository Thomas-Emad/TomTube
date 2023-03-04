<?php
session_start();

$img_profile = 'someone.png';

if (isset($_SESSION['user'])) {
  $profile = 'profile.php?u=' . $_SESSION['user']['random_user'];
  $username = $_SESSION['user']['random_user'];
  $que = mysqli_query($db, "SELECT u.email, u.password, u.name_channle, u.img_profile, u.time_create, COUNT(v.`url_video`) FROM `users` u, `videos` v WHERE u.random_user = '$username';");
  $row = mysqli_fetch_row($que);
  $img_profile = $row[3];
} else {
  $profile = 'account.php?site=login';
}
// echo $img_profile;

// Header
$header = "
    <nav class='navbar navbar-expand-lg'>
      <div class='container'>
        <a class='navbar-brand' href='index.php?v=*' title='All Videos In Site.'>TomTube</a>
        <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#menu_show'>
          <span class='navbar-toggler-icon'></span>
        </button>
        <div class='collapse navbar-collapse' id='menu_show'>
          <ul class='navbar-nav me-auto mb-2 mb-lg-0'>
            <li class='nav-item'>
              <i class='fa-solid fa-house-chimney'></i>
              <a class='nav-link active' href='index.php' title='Your Home.'>Home</a>
            </li>
            <li class='nav-item'>
              <i class='fa-solid fa-user'></i>
              <a class='nav-link' href='$profile'>profile</a>
            </li>
          </ul>
          <form action='' method='POST' class='me-2'>
            <input type='submit' name='mode' value='Mode' title='Change Mode Site'> 
          </form>
          <form class='d-flex' role='search' method='GET' action='search.php'>
            <input class='form-control me-1' type='search' name='search' placeholder='Search'>
            <button class='btn btn-outline-success' type='submit'>Search</button>
            <a class='nav-link img_profile ms-2' href='$profile'><img src='img/img_users/$img_profile' alt=''></a>
          </form>
        </div>
      </div>
    </nav>";

// Mode Site
if (!isset($_COOKIE['mode'])) {
  setcookie('mode', 'light', (time() * 10));
  // header("Refresh:0");
}
if (isset($_POST['mode'])) {
  if (($_COOKIE['mode'] == 'light')) {
    setcookie('mode', 'dark', (time() * 10));
  } else {
    setcookie('mode', 'light', (time() * 10));
  }
  header('Refresh: 0');
}

$mode = @$_COOKIE['mode'];
$mode_dark = 'document.querySelector("style").innerHTML = `:root {--bg-color: #444; --bg-white: #333; --bg-c5: #555;} body{background-color: #222; color: #fff !important;} .text_color{color:#c7c7c7 !important;} .navbar-toggler{background-color: var(--bg-c5);}`;';
$mode_light = 'document.querySelector("style").innerHTML = `:root {--bg-color: #ffffff7d; --bg-white: #fff; --bg-c5: #c5c5c5;} body{background-color: #99999987; color: #000 !important;}`;';
