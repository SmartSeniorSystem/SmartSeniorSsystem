<?php
session_start();

if (isset($_GET['lang'])) {
    $_SESSION['user_lang'] = $_GET['lang'];
}

$lang = $_SESSION['user_lang'] ?? 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

$host = 'localhost';
$db   = 'uob_projectdb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // تم إضافة هذا السطر لجعل جلب البيانات أسهل
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function checkAuth($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: login.php");
        exit();
    }
}
?>