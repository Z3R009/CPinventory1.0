<?php
if ($_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>