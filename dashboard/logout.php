<?php
session_start();

session_unset();
session_destroy();

header("Location: /quiz_system/dashboard/login.php");
exit();
?>