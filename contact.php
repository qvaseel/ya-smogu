<?php
declare(strict_types=1);

// Обработчик формы для виртуального хостинга Timeweb.
// Не хранит пароли и не требует базы данных.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

// Honeypot: обычный посетитель это поле не видит и не заполняет.
if (!empty($_POST['bot-field'] ?? '')) {
    header('Location: /thanks.html', true, 303);
    exit;
}

function clean_line(string $value, int $max = 120): string {
    $value = trim($value);
    $value = str_replace(["\r", "\n", "\0"], ' ', $value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return mb_substr($value, 0, $max, 'UTF-8');
}

function clean_text(string $value, int $max = 1500): string {
    $value = trim($value);
    $value = str_replace("\0", '', $value);
    return mb_substr($value, 0, $max, 'UTF-8');
}

$name = clean_line((string)($_POST['name'] ?? ''), 80);
$phone = clean_line((string)($_POST['phone'] ?? ''), 50);
$topic = clean_line((string)($_POST['topic'] ?? 'Другое'), 100);
$message = clean_text((string)($_POST['message'] ?? ''), 1500);
$consent = (string)($_POST['consent'] ?? '');

if ($name === '' || $phone === '' || $consent !== 'yes') {
    header('Location: /?form=error#contact-form', true, 303);
    exit;
}

// Допускаем только понятный набор символов в телефоне и разумную длину.
if (!preg_match('/^[0-9+()\-\s]{6,50}$/', $phone)) {
    header('Location: /?form=error#contact-form', true, 303);
    exit;
}

$to = 'YaSmogu0501@gmail.com';
$subject = 'Новая заявка с сайта «Я смогу!»';

$body = "Новая заявка с сайта «Я смогу!»\n\n"
      . "Имя: {$name}\n"
      . "Телефон: {$phone}\n"
      . "Интересует: {$topic}\n\n"
      . "Комментарий:\n" . ($message !== '' ? $message : '—') . "\n\n"
      . "Дата: " . date('d.m.Y H:i:s') . "\n"
      . "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

// На Timeweb mail() отправляет письмо через серверный Exim.
// После подключения доменной почты лучше перейти на SMTP + DKIM.
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: =?UTF-8?B?' . base64_encode('Сайт «Я смогу!»') . '?= <noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>',
    'Reply-To: ' . $to,
    'X-Mailer: PHP/' . PHP_VERSION,
];

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$sent = @mail($to, $encodedSubject, $body, implode("\r\n", $headers));

if ($sent) {
    header('Location: /thanks.html', true, 303);
    exit;
}

header('Location: /?form=error#contact-form', true, 303);
exit;
