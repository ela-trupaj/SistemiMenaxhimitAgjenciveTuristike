<?php

declare(strict_types=1);

// Lejo ekzekutimin vetem nga terminali.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require dirname(__DIR__) . '/includes/bootstrap.php';

// ==========================================
// KONTROLLI I ARGUMENTEVE
// ==========================================

if ($argc !== 4) {
    echo "Usage: php tools/create_staff.php NAME EMAIL PASSWORD\n";
    exit(1);
}

[$script, $name, $email, $password] = $argv;

$name = trim($name);
$email = trim($email);

// ==========================================
// VALIDIMI
// ==========================================

if ($name === '' || mb_strlen($name) > 100) {
    echo "Invalid name.\n";
    exit(1);
}

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 190
) {
    echo "Invalid email.\n";
    exit(1);
}

if (strlen($password) < 12) {
    echo "Password must have at least 12 characters.\n";
    exit(1);
}

// ==========================================
// GJETJA E ROLIT STAF
// ==========================================

$stmt = $db->prepare(
    'SELECT id FROM rolet WHERE emri = ? LIMIT 1'
);

$stmt->execute(['Staf']);

$roleId = $stmt->fetchColumn();

if (!$roleId) {
    echo "Staff role missing in database.\n";
    exit(1);
}

// ==========================================
// KRIJIMI I LLOGARISE
// ==========================================

try {

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $db->prepare(
        'INSERT INTO perdoruesit
         (emri, email, fjalekalimi, roli_id)
         VALUES (?, ?, ?, ?)'
    );

    $stmt->execute([
        $name,
        $email,
        $passwordHash,
        (int) $roleId
    ]);

    echo "Staff account created successfully.\n";

} catch (PDOException $e) {

    if ($e->getCode() === '23000') {
        echo "Email already exists or database constraint failed.\n";
    } else {
        error_log($e->getMessage());
        echo "Could not create staff account.\n";
    }

    exit(1);
}
