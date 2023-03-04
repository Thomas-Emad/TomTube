<?php
include_once('db.php');

// Get All Information From Url.
$url_use = explode('&', explode('e=', $_SERVER['REQUEST_URI'])[1]);
$email = $url_use[0];
$v_code = explode('vc=', "$url_use[1]")[1];

// check Form Email, v_code In Url.
if (!isset($email) || !isset($v_code)) {
  header('Location: 404.php');
  exit();
}

$que = mysqli_query($db, "SELECT email, status FROM `users` WHERE `email` = '$email';");
$real_status = explode(',', mysqli_fetch_row($que)[1]);

if ($v_code === $real_status[1]) {
  $que = mysqli_query($db, "UPDATE `users` SET `status` = 'active,$v_code' WHERE `users`.`email` = '$email';");
  header('Location: index.php');
} else {
  header('Location: 404.php');
  exit();
}

// http://localhost/php/projects/tomtube/active_email.php?e=thomad.emad.shawky@gmail.com&vc=123456
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Active Email</title>
</head>

<body>
</body>

</html>