<?php
session_start();

// Hardcoded credentials for simplicity
$admin_user = 'admin';
$admin_pass = 'admin123';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Usuário ou senha incorretos.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - Copa Jiu-Jitsu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff1f1f;
            --bg-dark: #050505;
            --surface: #121212;
            --text-main: #ffffff;
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: var(--surface);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid var(--border);
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        h1 {
            font-family: 'Oswald';
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.8rem;
            color: #a0a0a0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            background: #1a1a1a;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'Oswald';
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background: #cc0000;
            transform: translateY(-2px);
        }

        .error-msg {
            background: rgba(255, 31, 31, 0.1);
            color: #ff4444;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 31, 31, 0.2);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h1>Área Restrita</h1>
        
        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Usuário</label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Entrar no Painel</button>
        </form>
    </div>

</body>
</html>
