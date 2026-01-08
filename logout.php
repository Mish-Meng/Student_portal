<?php
session_start();
session_unset();  // remove all session variables
session_destroy(); // destroy the session completely

// redirect to login page
header("Location: login.php");
exit();
?>
