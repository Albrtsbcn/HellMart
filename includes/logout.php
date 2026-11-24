<?php
session_start();
session_unset();
session_destroy();
header("Location: /HellMart/includes/login.php");
exit();
?>