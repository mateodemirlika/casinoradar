<?php
$query = $_SERVER['QUERY_STRING'] ?? '';

if (preg_match('/^url=(.+)$/', $query, $m)) {
    header('Referrer-Policy: origin');
    header('Location: ' . $m[1], true, 302);
    exit;
}

http_response_code(400);
exit;

