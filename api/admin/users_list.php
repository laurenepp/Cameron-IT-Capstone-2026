<?php
require_once "../utils.php";

header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_role("Administrator");

try {
    $stmt = $pdo->prepare("
        SELECT
            u.User_ID,
            u.First_Name,
            u.Last_Name,
            u.Email,
            u.Phone_Number,
            r.Role_Name,
            u.Is_Disabled
        FROM Users u
        JOIN Roles r
            ON u.Role_ID = r.Role_ID
        ORDER BY u.Last_Name, u.First_Name
    ");

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // return array directly (matches admin.js expectations)
    echo json_encode($users);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
}