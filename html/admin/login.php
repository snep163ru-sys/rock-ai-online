<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid login or password!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NEURA ADMIN - Login</title>
    <style>
        body {
            background: #030305;
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login {
            background: #0a0a10;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #00ffcc;
            box-shadow: 0 0 40px rgba(0,255,204,0.1);
            width: 350px;
            text-align: center;
        }
        h1 {
            color: #00ffcc;
            font-size: 1.8rem;
            margin-bottom: 30px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: #151520;
            border: 1px solid #2a2a3e;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #00ffcc;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #00ffcc;
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            transform: scale(1.02);
        }
        .error {
            color: #ff3333;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="login">
        <h1>NEURA ADMIN</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Login" value="Snep163ru" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
