<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$host = "localhost";
$user = "root";
$password = "";
$database = "taskflow";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>