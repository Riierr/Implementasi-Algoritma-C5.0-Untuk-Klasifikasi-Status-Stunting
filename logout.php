<?php
// logout.php
session_start();
session_destroy();

// Redirect ke halaman login
header('Location: informasi.php');
exit();
?>