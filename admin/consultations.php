<?php
/**
 * Admin - Consultations Management
 */
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pageTitle = 'Gestão de Consultas';
$success = '';
$error = '';

$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($token)) {
        $error = 'Token de segurança inválido.';
    } else {
        $consultationId = isset($_POST['consultation_id']) ? (int) $_POST['consultation_id'] : 0;
        $action = $_POST['action'];

        if ($consultationId > 0) {
            $validStatuses = ['pendente', 'confirmada', 'cancelada', 'concluida'];
            if ($action === 'update_status' && isset($_POST['status']) && in_array($_POST['status'], $validStatuses)) {
                $stmt = $db->prepare("UPDATE consultations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$_POST['status'], $consultationId]);
                $success = 'Estado da consulta atualizado.';
            } elseif ($action === 'delete') {
                $stmt = $db->prepare("DELETE FROM consultations WHERE id = ?");
                $stmt->execute([$consultationId]);
                $success = 'Consulta eliminada.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();

// Filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM consultations WHERE 1=1";
$params = [];

if ($statusFilter) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($searchFilter) {
    $query .= " AND (nome LIKE ? OR email LIKE ? OR servico LIKE ?)";
    $searchTerm = '%' . $searchFilter . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$consultations = $stmt->fetchAll();

// Detail view
$detail = null;
if (isset($_GET['view'])) {
    $viewId = (int) $_GET['view'];
    $stmt = $db->prepare("SELECT * FROM consultations WHERE id = ?");
    $stmt->execute([$viewId]);
    $detail = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($detail): ?>
<!-- Detail View -->
<div class="admin-panel">
    <div class="panel-header">
        <h2 class="panel-title">Consulta #<?php echo $detail['id']; ?> — <?php echo htmlspecialchars($detail['nome']); ?></h2>
        <a href="consultations.php" class="btn btn-sm btn-secondary">← Voltar</a>
    </div>
    <div class="panel-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Nome</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['nome']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['email']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Telefone</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['telefone']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">NIF</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['nif'] ?: '—'); ?></div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">Morada</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['morada'] ?: '—'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Serviço</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['servico']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Data Pretendida</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['data_consulta']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Horário</div>
                <div class="detail-value"><?php echo htmlspecialchars($detail['horario']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Estado</div>
                <div class="detail-value">
                    <?php
                    $statusMap = [
                        'pendente' => 'badge-pending',
                        'confirmada' => 'badge-confirmed',
                        'cancelada' => 'badge-cancelled',
                        'concluida' => 'badge-completed',
                    ];
                    $badgeClass = isset($statusMap[$detail['status']]) ? $statusMap[$detail['status']] : 'badge-pending';
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($detail['status']); ?></span>
                </div>
            </div>
            <?php if ($detail['mensagem']): ?>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">Mensagem</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($detail['mensagem'])); ?></div>
            </div>
            <?php endif; ?>
            <div class="detail-item">
                <div class="detail-label">Registado em</div>
                <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($detail['created_at'])); ?></div>
            </div>
        </div>

        <!-- Status Change -->
        <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <form method="POST" action="consultations.php" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="consultation_id" value="<?php echo $detail['id']; ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="confirmada">
                <button type="submit" class="btn btn-sm btn-primary">✓ Confirmar</button>
            </form>
            <form method="POST" action="consultations.php" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="consultation_id" value="<?php echo $detail['id']; ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="concluida">
                <button type="submit" class="btn btn-sm btn-secondary">✓ Concluída</button>
            </form>
            <form method="POST" action="consultations.php" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="consultation_id" value="<?php echo $detail['id']; ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="cancelada">
                <button type="submit" class="btn btn-sm btn-danger">✗ Cancelar</button>
            </form>
        </div>
    </div>
</div>

<?php else: ?>
<!-- List View -->

<!-- Filters -->
<div class="admin-panel">
    <div class="panel-body">
        <form method="GET" action="consultations.php" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="search">Pesquisar</label>
                <input type="text" id="search" name="search" placeholder="Nome, email ou serviço..."
                       value="<?php echo htmlspecialchars($searchFilter); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="pendente" <?php echo $statusFilter === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="confirmada" <?php echo $statusFilter === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                    <option value="concluida" <?php echo $statusFilter === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                    <option value="cancelada" <?php echo $statusFilter === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            <a href="consultations.php" class="btn btn-secondary btn-sm">Limpar</a>
        </form>
    </div>
</div>

<!-- Results -->
<div class="admin-panel">
    <div class="panel-header">
        <h2 class="panel-title">Consultas (<?php echo count($consultations); ?>)</h2>
    </div>
    <div class="panel-body" style="padding: 0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Serviço</th>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($consultations)): ?>
                    <tr class="empty-row">
                        <td colspan="8">Nenhuma consulta encontrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($consultations as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo htmlspecialchars($c['nome']); ?></td>
                            <td><?php echo htmlspecialchars($c['email']); ?></td>
                            <td><?php echo htmlspecialchars($c['servico']); ?></td>
                            <td><?php echo htmlspecialchars($c['data_consulta']); ?></td>
                            <td><?php echo htmlspecialchars($c['horario']); ?></td>
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
                            <td>
                                <a href="consultations.php?view=<?php echo $c['id']; ?>" class="btn btn-sm btn-secondary">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
