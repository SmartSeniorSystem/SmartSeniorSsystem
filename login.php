<?php
require 'config.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name_en'];
        $_SESSION['email'] = $user['email'];
        header($user['role'] == 'student' ? "Location: student_dashboard.php" : "Location: prof_dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UOB Project Hub - Login</title>
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fafc;
            --text: #1e293b;
            --card: #ffffff;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .auth-card {
            background: var(--card);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }
        h2 { font-weight: 800; margin-bottom: 10px; color: #0f172a; }
        p { color: #64748b; margin-bottom: 30px; font-size: 14px; }
        .input-group { margin-bottom: 15px; text-align: left; }
        label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #475569; text-transform: uppercase; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }
        button:hover { background-color: #1d4ed8; transform: translateY(-1px); }
        .error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: bold; }
        .switch { margin-top: 25px; font-size: 14px; color: #64748b; }
        .switch a { color: var(--primary); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Login to your UOB account</p>

        <?php if($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="user@stu.uob.bh" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit">Sign In</button>
        </form>

        <div class="switch">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>
</body>
</html>