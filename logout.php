<?php
session_start();

// Si el usuario confirma el logout
//if (isset($_POST['confirm_logout'])) {
    // Destruir la sesión 
//}
//session_unset();
session_destroy();
// Redirigir al login
header("Location: index.php");
exit(); 
?>
