<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/auth.php");
    exit();
}
?>