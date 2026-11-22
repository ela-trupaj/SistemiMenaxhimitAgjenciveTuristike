<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require dirname(__DIR__) . '/includes/entities.php';

// Pergjigjet e API-se jane ne format JSON.
header('Content-Type: application/json; charset=utf-8');

/** Kthen pergjigje JSON me statusin HTTP.*/
function response(int $status, array $data): never
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

    exit;
}

// AUTENTIKIMI DHE AUTORIZIMI
$user = current_user();

if (!$user) {
    response(401, [
        'success' => false,
        'error'   => 'UNAUTHENTICATED'
    ]);
}

// Vetem administratori ka qasje ne API.
if ((int) $user['roli_id'] !== 1) {
    response(403, [
        'success' => false,
        'error'   => 'FORBIDDEN'
    ]);
}

// IDENTIFIKIMI I MODULIT
$entity = (string) ($_GET['entity'] ?? '');

if (!isset($entities[$entity])) {
    response(404, [
        'success' => false,
        'error'   => 'MODULE_NOT_FOUND'
    ]);
}

$method = $_SERVER['REQUEST_METHOD'];

$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
) ?: null;

if ($method === 'GET') {

    // Leximi i nje regjistri sipas ID-se.
    if ($id !== null) {

        $stmt = $db->prepare(
            'SELECT * FROM ' . $entity . ' WHERE id = ?'
        );

        $stmt->execute([$id]);

        $record = $stmt->fetch();

        if (!$record) {
            response(404, [
                'success' => false,
                'error'   => 'NOT_FOUND'
            ]);
        }

        response(200, [
            'success' => true,
            'data'    => $record
        ]);
    }

    $stmt = $db->query($entities[$entity]['list']);

    $records = $stmt->fetchAll();

    response(200, [
        'success' => true,
        'data'    => $records
    ]);
}

// MBROJTJA CSRF
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!hash_equals(csrf(), (string) $token)) {
    response(419, [
        'success' => false,
        'error'   => 'INVALID_CSRF'
    ]);
}

// POST - SHTIMI I REGJISTRAVE
// PATCH - PERDITESIMI I REGJISTRAVE
if ($method === 'POST' || $method === 'PATCH') {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        response(400, [
            'success' => false,
            'error'   => 'INVALID_JSON'
        ]);
    }

    // PATCH kerkon nje ID te vlefshme.
    if ($method === 'PATCH' && $id === null) {
        response(400, [
            'success' => false,
            'error'   => 'ID_REQUIRED'
        ]);
    }

    // Kontrollojme nese regjistri ekziston.
    if ($method === 'PATCH') {

        $stmt = $db->prepare(
            'SELECT id FROM ' . $entity . ' WHERE id = ?'
        );

        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            response(404, [
                'success' => false,
                'error'   => 'NOT_FOUND'
            ]);
        }
    }

    // Validimi dhe ruajtja ne MySQL.
    $errors = persist_entity(
        $db,
        $entity,
        $entities[$entity]['fields'],
        $input,
        $method === 'PATCH' ? $id : null
    );

    // Gabimet e validimit.
    if ($errors) {
        response(422, [
            'success' => false,
            'errors'  => $errors
        ]);
    }

    // Pergjigjja pas ruajtjes.
    response(
        $method === 'POST' ? 201 : 200,
        [
            'success' => true,
            'message' => 'Ruajtur me sukses',
            'id'      => $method === 'POST'
                ? (int) $db->lastInsertId()
                : $id
        ]
    );
}

// FSHIRJA E REGJISTRAVE
if ($method === 'DELETE') {

    if ($id === null) {
        response(400, [
            'success' => false,
            'error'   => 'ID_REQUIRED'
        ]);
    }

    try {

        $stmt = $db->prepare(
            'DELETE FROM ' . $entity . ' WHERE id = ?'
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            response(404, [
                'success' => false,
                'error'   => 'NOT_FOUND'
            ]);
        }

        response(200, [
            'success' => true,
            'message' => 'Regjistri u fshi me sukses'
        ]);

    } catch (PDOException $e) {

        response(409, [
            'success' => false,
            'error'   => 'RELATED_RECORDS'
        ]);
    }
}

// METODAT E PALEJUARA
header('Allow: GET, POST, PATCH, DELETE');

response(405, [
    'success' => false,
    'error'   => 'METHOD_NOT_ALLOWED'
]);
