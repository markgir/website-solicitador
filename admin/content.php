<?php
/**
 * Admin - Content Management
 */
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pageTitle = 'Gestão de Conteúdo';
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($token)) {
        $error = 'Token de segurança inválido. Tente novamente.';
    } else {
        $section = isset($_POST['section']) ? $_POST['section'] : '';
        $lang = isset($_POST['lang']) ? $_POST['lang'] : 'pt';

        // Collect all content fields for this section
        $fields = isset($_POST['fields']) ? $_POST['fields'] : [];

        if ($section && is_array($fields)) {
            foreach ($fields as $key => $value) {
                updateContent($section, $key, $lang, $value);
            }
            // Export to JSON for front-end
            exportContentJSON();
            $success = 'Conteúdo atualizado com sucesso!';
        } else {
            $error = 'Dados inválidos.';
        }
    }
}

$csrfToken = generateCSRFToken();

// Get current content for both languages
$ptContent = [
    'hero' => getContent('hero', 'pt'),
    'services' => getContent('services', 'pt'),
    'about' => getContent('about', 'pt'),
    'contact' => getContent('contact', 'pt'),
    'footer' => getContent('footer', 'pt'),
];
$frContent = [
    'hero' => getContent('hero', 'fr'),
    'services' => getContent('services', 'fr'),
    'about' => getContent('about', 'fr'),
    'contact' => getContent('contact', 'fr'),
    'footer' => getContent('footer', 'fr'),
];

include __DIR__ . '/includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Language Tabs -->
<div class="admin-tabs">
    <button class="admin-tab active" data-tab="tab-pt">🇵🇹 Português</button>
    <button class="admin-tab" data-tab="tab-fr">🇫🇷 Français</button>
</div>

<!-- Portuguese Content -->
<div class="tab-content active" id="tab-pt">

    <!-- Hero Section -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Secção Principal (Hero)</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="hero">
                <input type="hidden" name="lang" value="pt">

                <div class="form-group">
                    <label for="pt-hero-title">Título Principal</label>
                    <input type="text" id="pt-hero-title" name="fields[title]"
                           value="<?php echo htmlspecialchars($ptContent['hero']['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="pt-hero-subtitle">Subtítulo / Descrição</label>
                    <textarea id="pt-hero-subtitle" name="fields[subtitle]" rows="3"><?php echo htmlspecialchars($ptContent['hero']['subtitle'] ?? ''); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pt-hero-cta1">Botão Principal</label>
                        <input type="text" id="pt-hero-cta1" name="fields[cta_primary]"
                               value="<?php echo htmlspecialchars($ptContent['hero']['cta_primary'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="pt-hero-cta2">Botão Secundário</label>
                        <input type="text" id="pt-hero-cta2" name="fields[cta_secondary]"
                               value="<?php echo htmlspecialchars($ptContent['hero']['cta_secondary'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Hero</button>
            </form>
        </div>
    </div>

    <!-- Services Section -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Secção de Serviços</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="services">
                <input type="hidden" name="lang" value="pt">

                <div class="form-group">
                    <label for="pt-services-title">Título da Secção</label>
                    <input type="text" id="pt-services-title" name="fields[title]"
                           value="<?php echo htmlspecialchars($ptContent['services']['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="pt-services-subtitle">Subtítulo</label>
                    <textarea id="pt-services-subtitle" name="fields[subtitle]" rows="2"><?php echo htmlspecialchars($ptContent['services']['subtitle'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Serviços</button>
            </form>
        </div>
    </div>

    <!-- About Section -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Secção Sobre Nós</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="about">
                <input type="hidden" name="lang" value="pt">

                <div class="form-group">
                    <label for="pt-about-title">Título</label>
                    <input type="text" id="pt-about-title" name="fields[title]"
                           value="<?php echo htmlspecialchars($ptContent['about']['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="pt-about-text">Texto (use parágrafos separados por linhas em branco)</label>
                    <textarea id="pt-about-text" name="fields[text]" rows="6"><?php echo htmlspecialchars($ptContent['about']['text'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="pt-about-cta">Texto do Botão CTA</label>
                    <input type="text" id="pt-about-cta" name="fields[cta]"
                           value="<?php echo htmlspecialchars($ptContent['about']['cta'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Guardar Sobre Nós</button>
            </form>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Informações de Contacto</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="contact">
                <input type="hidden" name="lang" value="pt">

                <div class="form-row">
                    <div class="form-group">
                        <label for="pt-contact-email">Email</label>
                        <input type="email" id="pt-contact-email" name="fields[email]"
                               value="<?php echo htmlspecialchars($ptContent['contact']['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="pt-contact-phone">Telefone</label>
                        <input type="text" id="pt-contact-phone" name="fields[phone]"
                               value="<?php echo htmlspecialchars($ptContent['contact']['phone'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Contacto</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Rodapé</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="footer">
                <input type="hidden" name="lang" value="pt">

                <div class="form-group">
                    <label for="pt-footer-desc">Descrição do Rodapé</label>
                    <textarea id="pt-footer-desc" name="fields[description]" rows="3"><?php echo htmlspecialchars($ptContent['footer']['description'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Rodapé</button>
            </form>
        </div>
    </div>

</div>

<!-- French Content -->
<div class="tab-content" id="tab-fr">

    <!-- Hero Section -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Section Principale (Hero)</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="hero">
                <input type="hidden" name="lang" value="fr">

                <div class="form-group">
                    <label for="fr-hero-title">Titre Principal</label>
                    <input type="text" id="fr-hero-title" name="fields[title]"
                           value="<?php echo htmlspecialchars($frContent['hero']['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="fr-hero-subtitle">Sous-titre / Description</label>
                    <textarea id="fr-hero-subtitle" name="fields[subtitle]" rows="3"><?php echo htmlspecialchars($frContent['hero']['subtitle'] ?? ''); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="fr-hero-cta1">Bouton Principal</label>
                        <input type="text" id="fr-hero-cta1" name="fields[cta_primary]"
                               value="<?php echo htmlspecialchars($frContent['hero']['cta_primary'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="fr-hero-cta2">Bouton Secondaire</label>
                        <input type="text" id="fr-hero-cta2" name="fields[cta_secondary]"
                               value="<?php echo htmlspecialchars($frContent['hero']['cta_secondary'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Sauvegarder Hero</button>
            </form>
        </div>
    </div>

    <!-- Services Section -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Section Services</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="services">
                <input type="hidden" name="lang" value="fr">

                <div class="form-group">
                    <label for="fr-services-title">Titre de la Section</label>
                    <input type="text" id="fr-services-title" name="fields[title]"
                           value="<?php echo htmlspecialchars($frContent['services']['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="fr-services-subtitle">Sous-titre</label>
                    <textarea id="fr-services-subtitle" name="fields[subtitle]" rows="2"><?php echo htmlspecialchars($frContent['services']['subtitle'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Sauvegarder Services</button>
            </form>
        </div>
    </div>

    <!-- About Section -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Section À Propos</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="about">
                <input type="hidden" name="lang" value="fr">

                <div class="form-group">
                    <label for="fr-about-title">Titre</label>
                    <input type="text" id="fr-about-title" name="fields[title]"
                           value="<?php echo htmlspecialchars($frContent['about']['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="fr-about-text">Texte (paragraphes séparés par des lignes vides)</label>
                    <textarea id="fr-about-text" name="fields[text]" rows="6"><?php echo htmlspecialchars($frContent['about']['text'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="fr-about-cta">Texte du Bouton CTA</label>
                    <input type="text" id="fr-about-cta" name="fields[cta]"
                           value="<?php echo htmlspecialchars($frContent['about']['cta'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Sauvegarder À Propos</button>
            </form>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Informations de Contact</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="contact">
                <input type="hidden" name="lang" value="fr">

                <div class="form-row">
                    <div class="form-group">
                        <label for="fr-contact-email">Email</label>
                        <input type="email" id="fr-contact-email" name="fields[email]"
                               value="<?php echo htmlspecialchars($frContent['contact']['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="fr-contact-phone">Téléphone</label>
                        <input type="text" id="fr-contact-phone" name="fields[phone]"
                               value="<?php echo htmlspecialchars($frContent['contact']['phone'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Sauvegarder Contact</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2 class="panel-title">Pied de Page</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="content.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="section" value="footer">
                <input type="hidden" name="lang" value="fr">

                <div class="form-group">
                    <label for="fr-footer-desc">Description du Pied de Page</label>
                    <textarea id="fr-footer-desc" name="fields[description]" rows="3"><?php echo htmlspecialchars($frContent['footer']['description'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Sauvegarder Pied de Page</button>
            </form>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
