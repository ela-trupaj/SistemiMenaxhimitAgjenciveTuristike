<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$search = trim($_GET['q'] ?? '');

if ($search === '') {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT id, emri, shteti
        FROM destinacionet
        WHERE emri LIKE :emri
           OR shteti LIKE :shteti
        ORDER BY emri ASC
        LIMIT 10
    ");

    $stmt->execute([
        'emri' => '%' . $search . '%',
        'shteti' => '%' . $search . '%'
    ]);

    echo json_encode(
        $stmt->fetchAll(),
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'error' => 'Kërkimi i destinacioneve dështoi.'
    ]);
}