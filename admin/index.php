<?php
/**
 * Admin Login Page
 */
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (attemptLogin($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Credenciais inválidas. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Backoffice · Solicitador</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo">Solicitad<span>o</span>r</div>
            <p class="login-subtitle">Acesso ao Backoffice</p>

            <?php if ($error): ?>
                <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="form-group">
                    <label for="username">Utilizador</label>
                    <input type="text" id="username" name="username" required autocomplete="username" autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Palavra-passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
