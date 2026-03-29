<?php
require_once "../config.php";

header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        "error" => "Not logged in"
    ]);
    exit;
}

echo json_encode($_SESSION['user']);