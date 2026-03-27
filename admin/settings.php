<?php
/**
 * Admin - Settings (Password change)
 */
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pageTitle = 'Definições';
$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($token)) {
        $error = 'Token de segurança inválido.';
    } else {
        $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Por favor, preencha todos os campos.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'A nova palavra-passe deve ter pelo menos 6 caracteres.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'A nova palavra-passe e a confirmação não coincidem.';
        } elseif (changePassword($_SESSION['admin_user_id'], $currentPassword, $newPassword)) {
            $success = 'Palavra-passe alterada com sucesso!';
        } else {
            $error = 'Palavra-passe atual incorreta.';
        }
    }
}

// Handle content export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export_content') {
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($token)) {
        $error = 'Token de segurança inválido.';
    } else {
        exportContentJSON();
        $success = 'Conteúdo exportado para JSON com sucesso!';
    }
}

$csrfToken = generateCSRFToken();

include __DIR__ . '/includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Change Password -->
<div class="admin-panel">
    <div class="panel-header">
        <h2 class="panel-title">Alterar Palavra-passe</h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="settings.php" style="max-width: 400px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label for="current_password">Palavra-passe Atual</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label for="new_password">Nova Palavra-passe</label>
                <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                <span class="form-help">Mínimo de 6 caracteres.</span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Nova Palavra-passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">Alterar Palavra-passe</button>
        </form>
    </div>
</div>

<!-- Export Content -->
<div class="admin-panel">
    <div class="panel-header">
        <h2 class="panel-title">Exportar Conteúdo</h2>
    </div>
    <div class="panel-body">
        <p style="margin-bottom: 1rem; color: var(--admin-text-muted); font-size: 0.9rem;">
            Exporta o conteúdo do backoffice para ficheiros JSON que são utilizados pelo front office do website.
        </p>
        <form method="POST" action="settings.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="export_content">
            <button type="submit" class="btn btn-primary">Exportar para JSON</button>
        </form>
    </div>
</div>

<!-- Info -->
<div class="admin-panel">
    <div class="panel-header">
        <h2 class="panel-title">Informações do Sistema</h2>
    </div>
    <div class="panel-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Utilizador</div>
                <div class="detail-value"><?php echo htmlspecialchars(getAdminName()); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">PHP</div>
                <div class="detail-value"><?php echo phpversion(); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Base de Dados</div>
                <div class="detail-value">SQLite 3</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Credenciais Padrão</div>
                <div class="detail-value">admin / admin123 (altere imediatamente)</div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
