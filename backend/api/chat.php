<?php
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];
$text = trim((string) ($body['message'] ?? ''));
if ($text === '') {
    echo json_encode(['ok' => false, 'reply' => 'Bạn hãy nhập nội dung nhé.'], JSON_UNESCAPED_UNICODE);
    return;
}

$_SESSION['_chat_last'] = $_SESSION['_chat_last'] ?? 0;
if (time() - (int) $_SESSION['_chat_last'] < 1) {
    echo json_encode(['ok' => true, 'reply' => 'Bạn gửi chậm 1 chút nhé 🙂'], JSON_UNESCAPED_UNICODE);
    return;
}
$_SESSION['_chat_last'] = time();

$reply = chatbot_reply($text);
echo json_encode(['ok' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
