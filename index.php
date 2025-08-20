<?php
session_start();
require_once './config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    if ($username_input === $valid_username && password_verify($password_input, $hashed_password)) {
        $_SESSION['logged_in'] = true;
        header('Location: list.php');
        exit;
    } else {
        $error = "معلومات المستخدم غير صحيحة";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Badge control</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=0">
  <link rel="shortcut icon" href="favicon.ico" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
  <link href="css/plugins.bundle.css" rel="stylesheet" type="text/css" />
  <link href="css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank bg-white">
<div class="d-flex flex-column flex-root" id="kt_app_root">
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
<div class="d-flex flex-row-fluid">
<div class="d-flex flex-column flex-center p-10 w-100 h-100">
<div class="d-flex flex-column flex-column-fluid flex-center w-100 p-0 mx-auto h-100">
<div class="d-flex justify-content-between flex-column-fluid flex-column flex-center w-100">

 
<div class="w-400px h-100 d-flex flex-center">
        <div class="d-flex flex-center flex-column flex-row-fluid">
          <img class="mx-auto mw-100 w-250px mb-10" src="logo.svg" alt="">
<!--begin::Login-->
<form method="post" class="form w-100" style="direction:rtl" action="">
  <?php if (isset($error)) echo "<div class='text-danger fs-4 mb-5 text-center'>$error</div>"; ?>
  <div class="fv-row mb-5">
    <input type="text" class="form-control form-control-solid" placeholder="اسم المستخدم" value="" id="username" name="username" required />
  </div>
  <div class="fv-row mb-5">
    <input type="password" class="form-control form-control-solid" placeholder="كلمة المرور" value="" id="password" name="password" required />
  </div>
  <div class="text-center pt-5">
    <button type="submit" class="btn btn-primary w-100" name="login">تسجيل الدخول</button>
  </div>
</form>
<!--end::Login-->
</div>
</div>



<div class="d-flex flex-column w-900px">
    <div class="separator separator-dashed my-10"></div>
    <div class="d-flex flex-stack">
        <div class="d-flex text-dark">
        Developed by &nbsp;<a href="mailto:aba@aba.sa">ABA</a>
        </div>
        <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
        <li class="menu-item"></li>
        </ul>
    </div>
</div>

</div>
</div>
</div>
</div>
</div>
</div>



  <script src="js/plugins.bundle.js"></script>
  <script src="js/scripts.bundle.js"></script>
</body>
</html>