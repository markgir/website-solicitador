<?php
/**
 * Solicitador Website - Simple Contact Form Handler
 *
 * Processes general contact messages from the contacts page.
 */

header('Content-Type: application/json; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

// Configuration
$adminEmail = 'info@example.com';
$siteName   = 'Solicitador - Serviços Jurídicos';

// Sanitize input
function sanitizeInput($value) {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

// Collect and sanitize form data
$nome     = isset($_POST['nome'])     ? sanitizeInput($_POST['nome'])     : '';
$email    = isset($_POST['email'])    ? sanitizeInput($_POST['email'])    : '';
$telefone = isset($_POST['telefone']) ? sanitizeInput($_POST['telefone']) : '';
$assunto  = isset($_POST['assunto'])  ? sanitizeInput($_POST['assunto'])  : '';
$mensagem = isset($_POST['mensagem']) ? sanitizeInput($_POST['mensagem']) : '';
$consent  = isset($_POST['consent'])  ? true : false;

// Validation
$errors = [];

if (empty($nome)) {
    $errors[] = 'Nome é obrigatório.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email válido é obrigatório.';
}

if (empty($assunto)) {
    $errors[] = 'Selecione um assunto.';
}

if (empty($mensagem)) {
    $errors[] = 'Mensagem é obrigatória.';
}

if (!$consent) {
    $errors[] = 'Deve aceitar a política de privacidade.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Prepare email content
$subject = "Contacto Website - $assunto - $nome";

$body = "
=== NOVO CONTACTO VIA WEBSITE ===

Nome:     $nome
Email:    $email
Telefone: $telefone
Assunto:  $assunto

MENSAGEM:
$mensagem

---
Consentimento RGPD: Aceite
Este contacto foi enviado através do formulário do website.
";

// Email headers
$headers  = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
$mailSent = mail($adminEmail, $subject, $body, $headers);

// Client confirmation email
$clientSubject = "$siteName - Confirmação do seu Contacto";
$clientBody = "
Caro/a $nome,

Obrigado pelo seu contacto.

Recebemos a sua mensagem com o assunto: $assunto

Iremos responder ao seu pedido no prazo máximo de 24 horas úteis.

Com os melhores cumprimentos,
$siteName
";

$clientHeaders  = "From: $adminEmail\r\n";
$clientHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($email, $clientSubject, $clientBody, $clientHeaders);

echo json_encode([
    'success' => true,
    'message' => 'Mensagem enviada com sucesso. Entraremos em contacto brevemente.'
]);
