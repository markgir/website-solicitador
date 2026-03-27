<?php
/**
 * Database connection and setup (SQLite)
 */

function getDB() {
    static $db = null;
    if ($db !== null) {
        return $db;
    }

    $dbPath = __DIR__ . '/../../data/solicitador.db';
    $dbDir  = dirname($dbPath);

    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }

    $isNew = !file_exists($dbPath);
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    if ($isNew) {
        initDatabase($db, $dbDir);
    }

    return $db;
}

function initDatabase(PDO $db, $dbDir) {
    // Admin users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Site content table (key-value with language support)
    $db->exec("
        CREATE TABLE IF NOT EXISTS site_content (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            section TEXT NOT NULL,
            field_key TEXT NOT NULL,
            lang TEXT NOT NULL DEFAULT 'pt',
            content TEXT NOT NULL DEFAULT '',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(section, field_key, lang)
        )
    ");

    // Consultations / bookings table
    $db->exec("
        CREATE TABLE IF NOT EXISTS consultations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL,
            telefone TEXT NOT NULL,
            nif TEXT DEFAULT '',
            morada TEXT DEFAULT '',
            servico TEXT NOT NULL,
            data_consulta TEXT NOT NULL,
            horario TEXT NOT NULL,
            mensagem TEXT DEFAULT '',
            status TEXT DEFAULT 'pendente',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create default admin user with a random initial password
    // The password is stored in a setup file that is displayed once during first login
    $initialPassword = bin2hex(random_bytes(6)); // 12 character hex password
    $defaultPassword = password_hash($initialPassword, PASSWORD_DEFAULT);
    $db->prepare("INSERT OR IGNORE INTO admin_users (username, password_hash, name) VALUES (?, ?, ?)")
       ->execute(['admin', $defaultPassword, 'Administrador']);

    // Store initial password for first-login display
    $setupFile = $dbDir . '/.setup_password';
    file_put_contents($setupFile, $initialPassword);

    // Seed default content
    seedDefaultContent($db);
}

function seedDefaultContent(PDO $db) {
    $defaults = [
        // Portuguese content
        ['hero', 'title', 'pt', 'Assessoria Jurídica de Confiança para Portugueses e Emigrantes'],
        ['hero', 'subtitle', 'pt', 'Acompanhamos cidadãos portugueses residentes e emigrantes em todas as questões jurídicas e administrativas. Resolva os seus assuntos em Portugal com segurança e proximidade, onde quer que esteja.'],
        ['hero', 'cta_primary', 'pt', 'Marcar Consulta'],
        ['hero', 'cta_secondary', 'pt', 'Ver Serviços'],
        ['services', 'title', 'pt', 'Áreas de Atuação'],
        ['services', 'subtitle', 'pt', 'Oferecemos um leque completo de serviços jurídicos adaptados às necessidades de quem vive em Portugal e no estrangeiro.'],
        ['about', 'title', 'pt', 'Ao Seu Lado, Onde Quer Que Esteja'],
        ['about', 'text', 'pt', "Somos um gabinete de solicitadoria dedicado a servir cidadãos portugueses residentes em Portugal e emigrantes espalhados pelo mundo — em especial na comunidade francófona. Sabemos que a distância não deve ser um obstáculo quando se trata de resolver questões jurídicas e administrativas no seu país de origem.\n\nCom atendimento personalizado em português e francês, garantimos uma comunicação clara e eficaz em cada etapa do processo. Desde a obtenção de documentos até à gestão de heranças, compra de imóveis ou questões de nacionalidade, tratamos de tudo para que não precise de se deslocar.\n\nConte connosco para proteger os seus direitos e interesses em Portugal, com a dedicação e a proximidade que merece."],
        ['about', 'cta', 'pt', 'Agendar Consulta Gratuita'],
        ['contact', 'email', 'pt', 'info@solicitador.pt'],
        ['contact', 'phone', 'pt', '+351 200 000 000'],
        ['footer', 'description', 'pt', 'Serviços de solicitadoria para cidadãos portugueses e emigrantes. Atendimento em português e francês.'],

        // French content
        ['hero', 'title', 'fr', 'Services Juridiques pour Portugais et Expatriés'],
        ['hero', 'subtitle', 'fr', 'Nous accompagnons les citoyens portugais résidents et expatriés francophones dans toutes leurs démarches juridiques et administratives au Portugal. Réglez vos affaires en toute sécurité et proximité, où que vous soyez.'],
        ['hero', 'cta_primary', 'fr', 'Prendre Rendez-vous'],
        ['hero', 'cta_secondary', 'fr', 'Voir les Services'],
        ['services', 'title', 'fr', "Domaines d'Intervention"],
        ['services', 'subtitle', 'fr', "Nous proposons une gamme complète de services juridiques adaptés aux besoins de ceux qui vivent au Portugal et à l'étranger."],
        ['about', 'title', 'fr', 'À Vos Côtés, Où Que Vous Soyez'],
        ['about', 'text', 'fr', "Notre cabinet est dédié aux citoyens portugais résidents au Portugal ainsi qu'aux expatriés et luso-descendants installés dans le monde francophone — en France, en Suisse et ailleurs. Nous savons que la distance ne doit pas être un obstacle lorsqu'il s'agit de résoudre des questions juridiques et administratives dans votre pays d'origine.\n\nAvec un accompagnement personnalisé en portugais et en français, nous garantissons une communication claire et efficace à chaque étape du processus. De l'obtention de documents à la gestion de successions, en passant par l'achat immobilier ou les questions de nationalité, nous nous occupons de tout pour que vous n'ayez pas à vous déplacer.\n\nComptez sur nous pour protéger vos droits et vos intérêts au Portugal, avec le dévouement et la proximité que vous méritez."],
        ['about', 'cta', 'fr', 'Prendre Rendez-vous Gratuit'],
        ['contact', 'email', 'fr', 'info@solicitador.pt'],
        ['contact', 'phone', 'fr', '+351 200 000 000'],
        ['footer', 'description', 'fr', 'Services juridiques pour citoyens portugais et expatriés. Accompagnement en portugais et en français.'],
    ];

    $stmt = $db->prepare("INSERT OR IGNORE INTO site_content (section, field_key, lang, content) VALUES (?, ?, ?, ?)");
    foreach ($defaults as $row) {
        $stmt->execute($row);
    }
}

/**
 * Get content for a section and language
 */
function getContent($section, $lang = 'pt') {
    $db = getDB();
    $stmt = $db->prepare("SELECT field_key, content FROM site_content WHERE section = ? AND lang = ?");
    $stmt->execute([$section, $lang]);
    $rows = $stmt->fetchAll();
    $result = [];
    foreach ($rows as $row) {
        $result[$row['field_key']] = $row['content'];
    }
    return $result;
}

/**
 * Update content field
 */
function updateContent($section, $fieldKey, $lang, $content) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO site_content (section, field_key, lang, content, updated_at)
        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
        ON CONFLICT(section, field_key, lang)
        DO UPDATE SET content = excluded.content, updated_at = CURRENT_TIMESTAMP
    ");
    return $stmt->execute([$section, $fieldKey, $lang, $content]);
}

/**
 * Export content to JSON files for front-end consumption
 */
function exportContentJSON() {
    $db = getDB();
    $stmt = $db->query("SELECT section, field_key, lang, content FROM site_content ORDER BY lang, section, field_key");
    $rows = $stmt->fetchAll();

    $data = [];
    foreach ($rows as $row) {
        $lang = $row['lang'];
        $section = $row['section'];
        $key = $row['field_key'];
        $data[$lang][$section][$key] = $row['content'];
    }

    $dataDir = __DIR__ . '/../../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }

    foreach ($data as $lang => $sections) {
        $filePath = $dataDir . '/content-' . $lang . '.json';
        file_put_contents($filePath, json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    return true;
}
