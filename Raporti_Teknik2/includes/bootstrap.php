<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// KONFIGURIMI I DATABAZES
$cfg = require dirname(__DIR__) . '/database/config.php';

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $cfg['host'],
        $cfg['port'],
        $cfg['database']
    );

    $db = new PDO(
        $dsn,
        $cfg['user'],
        $cfg['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {
    http_response_code(503);

    exit(
        'Lidhja me databazen deshtoi. ' .
        'Kontrolloni database/config.php dhe README.'
    );
}

// ==========================================
// SIGURIA E TE DHENAVE
// ==========================================

function e($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

/**Gjeneron ose kthen CSRF token.*/
function csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

/**Verifikon CSRF token nga formulari.*/
function verify_csrf(): void
{
    $token = (string) ($_POST['csrf'] ?? '');

    if (!hash_equals(csrf(), $token)) {
        http_response_code(419);
        exit('CSRF token i pavlefshem.');
    }
}

// ==========================================
// AUTENTIKIMI
// ==========================================

/**Kthen perdoruesin aktual nga sesioni.*/
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**Kerkon qe perdoruesi te jete i kycur.*/
function require_login(): void
{
    if (!current_user()) {
        header('Location: index.php');
        exit;
    }
}

// ==========================================
// AUTORIZIMI SIPAS ROLEVE
// ==========================================

// ID-te e roleve sipas databazes 
const ROLE_ADMIN = 1;
const ROLE_STAFF = 2;
const ROLE_CLIENT = 3;

/** Kontrollon nese perdoruesi ka nje nga rolet e lejuara.*/
function require_role(array $allowedRoles): void
{
    require_login();

    $roleId = (int) (current_user()['roli_id'] ?? 0);

    if (!in_array($roleId, $allowedRoles, true)) {
        http_response_code(403);
        exit('Nuk keni autorizim per kete faqe.');
    }
}

/**Lejon vetem administratorin.*/
function require_admin(): void
{
    require_role([ROLE_ADMIN]);
}

/** Lejon administratorin dhe stafin.*/
function require_staff(): void
{
    require_role([ROLE_ADMIN, ROLE_STAFF]);
}

/**Lejon vetem klientin.*/
function require_client(): void
{
    require_role([ROLE_CLIENT]);
}

// ==========================================
// NJOFTIMET DHE REDIRECT
// ==========================================

/** Ruan nje mesazh ne sesion.*/
function flash(string $message, string $kind = 'ok'): void
{
    $_SESSION['flash'] = [$message, $kind];
}

/** Ridrejton perdoruesin ne nje faqe.*/
function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

// ==========================================
// TE DHENAT PER SELECT
// ==========================================

function options(PDO $db, string $table, string $label): array
{
    $sql = 'SELECT id, ' . $label .
           ' AS label FROM ' . $table .
           ' ORDER BY id DESC';

    return $db->query($sql)->fetchAll();
}
