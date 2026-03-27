<?php
/**
 * Admin Dashboard
 */
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pageTitle = 'Painel de Controlo';

// Get stats
$db = getDB();

$totalConsultations = $db->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
$pendingConsultations = $db->query("SELECT COUNT(*) FROM consultations WHERE status = 'pendente'")->fetchColumn();
$contentSections = $db->query("SELECT COUNT(DISTINCT section) FROM site_content")->fetchColumn();
$lastUpdated = $db->query("SELECT MAX(updated_at) FROM site_content")->fetchColumn();

// Recent consultations
$recentConsultations = $db->query("SELECT * FROM consultations ORDER BY created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo $totalConsultations; ?></div>
        <div class="stat-label">Total de Consultas</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-number"><?php echo $pendingConsultations; ?></div>
        <div class="stat-label">Consultas Pendentes</div>
    </div>
    <div class="stat-card success">
        <div class="stat-number"><?php echo $contentSections; ?></div>
        <div class="stat-label">Secções de Conteúdo</div>
    </div>
    <div class="stat-card info">
        <div class="stat-number"><?php echo $lastUpdated ? date('d/m', strtotime($lastUpdated)) : '—'; ?></div>
        <div class="stat-label">Última Atualização</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-panel">
    <div class="panel-header">
        <h2 class="panel-title">Ações Rápidas</h2>
    </div>
    <div class="panel-body">
        <div class="quick-actions">
            <a href="content.php" class="quick-action">
                <span class="quick-action-icon">📝</span>
                <span class="quick-action-text">Editar Conteúdo</span>
            </a>
            <a href="consultations.php" class="quick-action">
                <span class="quick-action-icon">📅</span>
                <span class="quick-action-text">Gerir Consultas</span>
            </a>
            <a href="settings.php" class="quick-action">
                <span class="quick-action-icon">⚙️</span>
                <span class="quick-action-text">Definições</span>
            </a>
            <a href="../index.html" target="_blank" class="quick-action">
                <span class="quick-action-icon">🌐</span>
                <span class="quick-action-text">Ver Website</span>
            </a>
        </div>
    </div>
</div>

<!-- Recent Consultations -->
<div class="admin-panel">
    <div class="panel-header">
        <h2 class="panel-title">Consultas Recentes</h2>
        <a href="consultations.php" class="btn btn-sm btn-secondary">Ver Todas</a>
    </div>
    <div class="panel-body" style="padding: 0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Serviço</th>
                    <th>Data</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentConsultations)): ?>
                    <tr class="empty-row">
                        <td colspan="4">Nenhuma consulta registada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentConsultations as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['nome']); ?></td>
                            <td><?php echo htmlspecialchars($c['servico']); ?></td>
                            <td><?php echo htmlspecialchars($c['data_consulta']); ?></td>
                            <td>
                                <?php
                                $statusMap = [
                                    'pendente' => 'badge-pending',
                                    'confirmada' => 'badge-confirmed',
                                    'cancelada' => 'badge-cancelled',
                                    'concluida' => 'badge-completed',
                                ];
                                $badgeClass = isset($statusMap[$c['status']]) ? $statusMap[$c['status']] : 'badge-pending';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($c['status']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
