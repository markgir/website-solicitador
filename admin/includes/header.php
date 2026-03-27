<?php
/**
 * Admin header template
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice · Solicitador</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">Solicitad<span>o</span>r</a>
                <span class="sidebar-badge">Backoffice</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <span class="sidebar-icon">📊</span> Painel
                </a>
                <a href="content.php" class="sidebar-link <?php echo $currentPage === 'content' ? 'active' : ''; ?>">
                    <span class="sidebar-icon">📝</span> Conteúdo
                </a>
                <a href="consultations.php" class="sidebar-link <?php echo $currentPage === 'consultations' ? 'active' : ''; ?>">
                    <span class="sidebar-icon">📅</span> Consultas
                </a>
                <a href="settings.php" class="sidebar-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <span class="sidebar-icon">⚙️</span> Definições
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="sidebar-icon">👤</span>
                    <span><?php echo htmlspecialchars(getAdminName()); ?></span>
                </div>
                <a href="logout.php" class="sidebar-link sidebar-logout">
                    <span class="sidebar-icon">🚪</span> Sair
                </a>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="admin-main">
            <header class="admin-topbar">
                <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Menu">☰</button>
                <h1 class="topbar-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Backoffice'; ?></h1>
                <div class="topbar-actions">
                    <a href="../index.html" target="_blank" class="topbar-link">Ver Site →</a>
                </div>
            </header>
            <main class="admin-content">
