<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // In production, use password_verify($password, $user['password'])
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name_en'] = $user['full_name_en'];

        if ($user['role'] == 'professor') {
            header("Location: professor_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
    } else {
        header("Location: index.php?error=invalid");
    }
    exit();
}
?>