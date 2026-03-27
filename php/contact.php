<?php
/**
 * Solicitador Website - Contact / Consultation Form Handler
 *
 * Processes consultation booking requests, stores in database, and sends notification emails.
 */

header('Content-Type: application/json; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

// Configuration — replace with the actual solicitor email before deployment
$adminEmail = 'info@example.com';
$siteName   = 'Solicitador - Serviços Jurídicos';

// Include database for storing consultations
require_once __DIR__ . '/../admin/includes/db.php';

// Sanitize input
function sanitizeInput($value) {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

// Collect and sanitize form data
$nome       = isset($_POST['nome']) ? sanitizeInput($_POST['nome']) : '';
$email      = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$telefone   = isset($_POST['telefone']) ? sanitizeInput($_POST['telefone']) : '';
$nif        = isset($_POST['nif']) ? sanitizeInput($_POST['nif']) : '';
$morada     = isset($_POST['morada']) ? sanitizeInput($_POST['morada']) : '';
$servico    = isset($_POST['servico']) ? sanitizeInput($_POST['servico']) : '';
$data       = isset($_POST['data']) ? sanitizeInput($_POST['data']) : '';
$horario    = isset($_POST['horario']) ? sanitizeInput($_POST['horario']) : '';
$mensagem   = isset($_POST['mensagem']) ? sanitizeInput($_POST['mensagem']) : '';
$consent    = isset($_POST['consent']) ? true : false;

// Validation
$errors = [];

if (empty($nome)) {
    $errors[] = 'Nome completo é obrigatório.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email válido é obrigatório.';
}

if (empty($telefone)) {
    $errors[] = 'Telefone é obrigatório.';
}

if (!empty($nif) && !preg_match('/^\d{9}$/', $nif)) {
    $errors[] = 'NIF deve conter 9 dígitos.';
}

if (empty($servico)) {
    $errors[] = 'Selecione um serviço.';
}

if (empty($data)) {
    $errors[] = 'Selecione uma data para a consulta.';
} else {
    $dateObj = DateTime::createFromFormat('Y-m-d', $data);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $data) {
        $errors[] = 'Data inválida.';
    } elseif ($dateObj->getTimestamp() < strtotime('+2 days midnight')) {
        $errors[] = 'A data da consulta deve ser com pelo menos 2 dias de antecedência.';
    }
}

if (empty($horario)) {
    $errors[] = 'Selecione um horário.';
}

if (!$consent) {
    $errors[] = 'Deve aceitar a política de privacidade.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Store in database
$dbStored = false;
try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO consultations (nome, email, telefone, nif, morada, servico, data_consulta, horario, mensagem)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nome, $email, $telefone, $nif, $morada, $servico, $data, $horario, $mensagem]);
    $dbStored = true;
} catch (Exception $e) {
    error_log("Failed to store consultation in database: " . $e->getMessage());
}

// Prepare email content
$subject = "Nova Consulta - $servico - $nome";

$body = "
=== NOVO PEDIDO DE CONSULTA ===

DADOS DO CLIENTE
Nome: $nome
Email: $email
Telefone: $telefone
NIF: $nif
Morada: $morada

CONSULTA
Serviço: $servico
Data Pretendida: $data
Horário: $horario

MENSAGEM
$mensagem

---
Dados para emissão de recibo:
Nome: $nome
NIF: $nif
Morada: $morada
Email: $email
---

Este pedido foi enviado através do formulário do website.
Consentimento RGPD: Aceite
";

// Email headers — use trusted From address to prevent header injection
$headers  = "From: $adminEmail\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
$mailSent = mail($adminEmail, $subject, $body, $headers);

// Client confirmation email
$clientSubject = "$siteName - Confirmação do Pedido de Consulta";
$clientBody = "
Caro/a $nome,

Obrigado pelo seu pedido de consulta.

Recebemos o seu pedido para o serviço: $servico
Data pretendida: $data
Horário: $horario

PRÓXIMOS PASSOS:
1. Receberá a confirmação da data e hora da consulta por email no prazo de 24 horas.
2. Após confirmação, receberá instruções para o pagamento antecipado da consulta.
3. O pagamento deverá ser efetuado antes da data da consulta.
4. Após confirmação do pagamento, será emitido o respetivo recibo.

DADOS PARA FATURAÇÃO REGISTADOS:
Nome: $nome
NIF: $nif
Morada: $morada

Caso necessite de alterar ou cancelar a consulta, por favor contacte-nos com pelo menos 24 horas de antecedência.

Com os melhores cumprimentos,
$siteName
";

$clientHeaders  = "From: $adminEmail\r\n";
$clientHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($email, $clientSubject, $clientBody, $clientHeaders);

if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Pedido de consulta enviado com sucesso. Receberá confirmação por email.'
    ]);
} else {
    // Log error but still return success to user (email might be queued)
    error_log("Failed to send consultation email for: $email");
    echo json_encode([
        'success' => true,
        'message' => 'Pedido registado. Entraremos em contacto brevemente.'
    ]);
}
