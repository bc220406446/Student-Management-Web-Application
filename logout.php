<?php
// Remove all session data to log out the current user completely.
session_start();
session_unset();
session_destroy();

// Return the visitor to the shared login page.
header('Location: login.php');
exit;
?>
