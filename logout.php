<?php
// session destroy
session_start();
session_destroy();
header('Location: index.php');
exit;
?>