<?php
if ($_SESSION['user_type'] !== 'staff') {
    header('Location: login.php');
    exit;
}
?>