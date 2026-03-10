<?php
require 'config.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $role = "";
    if (str_ends_with($email, '@stu.uob.bh')) { $role = 'student'; }
    elseif (str_ends_with($email, '@uob.edu.bh')) { $role = 'professor'; }

    if ($role) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Users (full_name_en, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role]);
            header("Location: login.php?status=success");
            exit();
        } catch (PDOException $e) { $msg = "Email already exists!"; }
    } else {
        $msg = "Invalid UOB Domain!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UOB Project Hub - Register</title>
    <style>
        :root { --primary: #10b981; --bg: #f8fafc; --text: #1e293b; --card: #ffffff; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: var(--card); padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 380px; text-align: center; }
        h2 { font-weight: 800; margin-bottom: 10px; color: #0f172a; }
        p { color: #64748b; margin-bottom: 30px; font-size: 14px; }
        .input-group { margin-bottom: 15px; text-align: left; }
        label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #475569; text-transform: uppercase; }
        input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; outline: none; }
        button { width: 100%; padding: 14px; background-color: var(--primary); color: white; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .error { color: #dc2626; font-size: 13px; margin-bottom: 15px; font-weight: bold; }
        .switch { margin-top: 25px; font-size: 14px; color: #64748b; }
        .switch a { color: var(--primary); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Create Account</h2>
        <p>Join the academic project community</p>

        <?php if($msg): ?>
            <div class="error"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your name" required>
            </div>
            <div class="input-group">
                <label>UOB Email</label>
                <input type="email" name="email" placeholder="user@stu.uob.bh" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit">Register Now</button>
        </form>

        <div class="switch">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</body>
</html>