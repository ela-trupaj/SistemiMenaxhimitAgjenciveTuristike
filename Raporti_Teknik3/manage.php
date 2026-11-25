<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_staff();

require __DIR__ . '/includes/entities.php';
require __DIR__ . '/includes/layout.php';

// Marrja e modulit dhe veprimit
$entity = (string)($_GET['entity'] ?? '');
$action = (string)($_GET['action'] ?? 'list');
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

if (!isset($entities[$entity])) {
    http_response_code(404);
    exit('Moduli nuk ekziston.');
}
if (!in_array($action, ['list', 'create', 'edit'], true)) {
    http_response_code(404);
    exit('Veprim i panjohur.');
}

$meta = $entities[$entity];
$descriptions = [
    'destinacionet' => 'Organizo dhe përditëso destinacionet turistike në një vend.',
    'ofertat' => 'Krijo dhe menaxho ofertat për udhëtime të paharrueshme.',
    'rezervimet' => 'Monitoro dhe menaxho rezervimet e klientëve.',
    'pagesat' => 'Menaxho pagesat dhe përcill statusin e transaksioneve.'
];

$user = current_user();
$roleId = (int)($user['roli_id'] ?? 0);
$isAdmin = $roleId === ROLE_ADMIN;

// Autorizimet e stafit
$staffPermissions = [
    'destinacionet' => ['list'],
    'ofertat' => ['list', 'create', 'edit'],
    'rezervimet' => ['list', 'create', 'edit'],
    'pagesat' => ['list', 'create']
];

// Kontrollimi i autorizimeve
function can_manage(string $entity, string $action, bool $isAdmin, array $staffPermissions): bool
{
    if ($isAdmin) {
        return in_array($action, ['list', 'create', 'edit', 'delete'], true);
    }
    return in_array($action, $staffPermissions[$entity] ?? [], true);
}

if (!can_manage($entity, $action, $isAdmin, $staffPermissions)) {
    http_response_code(403);
    exit('Nuk keni autorizim për këtë veprim.');
}

$errors = [];
$record = [];
$rows = [];

// Perpunimi i formularit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $operation = (string)($_POST['operation'] ?? '');

    // Fshirja e regjistrit
    if ($operation === 'delete') {
        if (!can_manage($entity, 'delete', $isAdmin, $staffPermissions)) {
            http_response_code(403);
            exit('Nuk keni autorizim për fshirje.');
        }
        if (!$id) {
            http_response_code(400);
            exit('ID e pavlefshme.');
        }
        try {
            $stmt = $db->prepare('DELETE FROM ' . $entity . ' WHERE id = ?');
            $stmt->execute([$id]);
            flash($stmt->rowCount() ? 'Regjistri u fshi.' : 'Regjistri nuk ekziston.');
        } catch (PDOException $e) {
            flash('Nuk mund të fshihet: regjistri mund të jetë i lidhur me të dhëna të tjera.', 'error');
        }
        redirect(BASE_URL . 'manage.php?entity=' . $entity);
    }

    // Shtimi dhe ndryshimi i regjistrit
    if ($operation === 'save') {
        if ($action === 'create') {
            $requestedAction = 'create';
        } elseif ($action === 'edit') {
            $requestedAction = 'edit';
        } else {
            http_response_code(400);
            exit('Veprim i pavlefshëm për ruajtje.');
        }

        if (!can_manage($entity, $requestedAction, $isAdmin, $staffPermissions)) {
            http_response_code(403);
            exit('Nuk keni autorizim për ruajtje.');
        }
        if ($requestedAction === 'edit' && !$id) {
            http_response_code(400);
            exit('ID e pavlefshme.');
        }

        $errors = persist_entity(
            $db,
            $entity,
            $meta['fields'],
            $_POST,
            $requestedAction === 'edit' ? $id : null
        );

        if (!$errors) {
            flash('Të dhënat u ruajtën me sukses.');
            redirect(BASE_URL . 'manage.php?entity=' . $entity);
        }
    } elseif ($operation !== 'delete') {
        http_response_code(400);
        exit('Operacion i panjohur.');
    }
}

// Marrja e regjistrit per ndryshim
if ($action === 'edit') {
    if (!$id) {
        http_response_code(400);
        exit('ID e pavlefshme.');
    }
    $stmt = $db->prepare('SELECT * FROM ' . $entity . ' WHERE id = ?');
    $stmt->execute([$id]);
    $record = $stmt->fetch();

    if (!$record) {
        http_response_code(404);
        exit('Regjistri nuk ekziston.');
    }
}

// Marrja e regjistrave
if ($action === 'list') {
    $rows = $db->query($meta['list'])->fetchAll();
}

$canEdit = can_manage($entity, 'edit', $isAdmin, $staffPermissions);
$canDelete = can_manage($entity, 'delete', $isAdmin, $staffPermissions);

// Kontrollimi i moduleve qe kane dokumente PDF
$hasDocuments = in_array($entity, ['rezervimet', 'pagesat'], true);

page_top($meta['title']);
?>

<!-- Titulli i modulit -->
<div class="section-intro">
    <div>
        <div class="eyebrow dark">LUNÉA Voyages / MENAXHIMI</div>
        <h1><?= e($meta['title']) ?></h1>
        <p class="muted"><?= e($descriptions[$entity] ?? '') ?></p>
    </div>
    <?php if ($action === 'list'): ?>
        <?php if (can_manage($entity, 'create', $isAdmin, $staffPermissions)): ?>
            <a class="btn primary" href="?entity=<?= e($entity) ?>&action=create">
                <i class="bx bx-plus"></i>
                Shto të re
            </a>
        <?php endif; ?>
    <?php else: ?>
        <a class="btn ghost" href="?entity=<?= e($entity) ?>">
            <i class="bx bx-arrow-back"></i>
            Kthehu
        </a>
    <?php endif; ?>
</div>

<?php if ($action === 'list'): ?>
    <!-- Lista e regjistrave -->
    <div class="panel manage-table">
        <div class="panel-head">
            <h2>
                Regjistrat
                <span class="count"><?= count($rows) ?></span>
            </h2>
        </div>
        <?php if ($rows): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php foreach ($meta['fields'] as $key => $f): ?>
                                <th><?= e($f[0]) ?></th>
                            <?php endforeach; ?>
                            <?php if ($hasDocuments): ?>
                                <th>Dokumenti</th>
                            <?php endif; ?>
                            <?php if ($canEdit || $canDelete): ?>
                                <th>Veprime</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td data-label="ID">
                                    #<?= e($r['id']) ?>
                                </td>
                                <?php foreach ($meta['fields'] as $key => $f): ?>
                                    <?php
                                    $display = $r[$key] ?? '';
                                    if (str_starts_with($f[1], 'select:')) {
                                        $displayKey = match ($key) {
                                            'destinacioni_id' => 'destinacioni',
                                            'perdoruesi_id' => 'perdoruesi',
                                            'oferta_id' => 'oferta',
                                            default => $key
                                        };
                                        $display = $r[$displayKey] ?? $display;
                                    }
                                    ?>
                                    <td data-label="<?= e($f[0]) ?>">
                                        <?= e($display) ?>
                                    </td>
                                <?php endforeach; ?>

                                <!-- Shkarkimi i dokumentit PDF -->
                                <?php if ($hasDocuments): ?>
                                    <td data-label="Dokumenti">
                                        <?php if ($entity === 'rezervimet' && ($r['statusi'] ?? '') === 'Konfirmuar'): ?>
                                            <a href="<?= e(BASE_URL) ?>documents/booking_pdf.php?id=<?= e($r['id']) ?>" class="btn btn-pdf" title="Shkarko konfirmimin e rezervimit">
                                                <i class="bx bxs-file-pdf"></i>
                                                PDF
                                            </a>
                                        <?php elseif ($entity === 'pagesat' && ($r['statusi'] ?? '') === 'Paguar'): ?>
                                            <a href="<?= e(BASE_URL) ?>documents/payment_pdf.php?id=<?= e($r['id']) ?>" class="btn btn-pdf" title="Shkarko dëshminë e pagesës">
                                                <i class="bx bxs-file-pdf"></i>
                                                PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="muted"> — </span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <!-- Veprimet mbi regjistrat -->
                                <?php if ($canEdit || $canDelete): ?>
                                    <td data-label="Veprime" class="actions">
                                        <?php if ($canEdit): ?>
                                            <a href="?entity=<?= e($entity) ?>&action=edit&id=<?= e($r['id']) ?>">
                                                <i class="bx bx-edit-alt"></i>
                                                Ndrysho
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form method="POST" class="delete-form"
                                                action="?entity=<?= e($entity) ?>&id=<?= e($r['id']) ?>"
                                                onsubmit="return confirm('A jeni të sigurt që doni ta fshini?')">
                                                <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                                <input type="hidden" name="operation" value="delete">
                                                <button class="danger-link" type="submit" data-loading="Duke fshirë...">
                                                    <i class="bx bx-trash"></i>
                                                    Fshi
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="dashboard-empty">
                <div class="dashboard-empty-icon">
                    <i class="bx bx-folder-open"></i>
                </div>
                <h3>Nuk ka ende të dhëna</h3>
                <p>Regjistrat e rinj do të shfaqen këtu.</p>
            </div>
        <?php endif; ?>
    </div>

<?php elseif (in_array($action, ['create', 'edit'], true)): ?>
    <!-- Formulari per shtim dhe ndryshim -->
    <div class="panel form-panel">
        <div class="panel-head">
            <h2>
                <?= $action === 'edit'
                    ? 'Ndrysho regjistrin'
                    : 'Shto regjistër të ri' ?>
            </h2>
        </div>
        <!-- Error State -->
        <?php if ($errors): ?>
            <div class="notice error" role="alert">
                <?= e(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="entity-form" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <input type="hidden" name="operation" value="save">
            <div class="form-grid">
                <?php foreach ($meta['fields'] as $key => $f): ?>
                    <?php
                    $v = $_POST[$key] ?? $record[$key] ?? '';
                    $type = $f[1];
                    ?>
                    <div class="field <?= $type === 'textarea' ? 'wide' : '' ?>">
                        <label for="<?= e($key) ?>">
                            <?= e($f[0]) ?>
                            <?= $f[2] ? '*' : '' ?>
                        </label>
                        <?php if ($type === 'textarea'): ?>
                            <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="4"><?= e($v) ?></textarea>
                        <?php elseif (str_starts_with($type, 'select:')): ?>
                            <?php $parts = explode(':', $type); ?>
                            <select id="<?= e($key) ?>" name="<?= e($key) ?>" <?= $f[2] ? 'required' : '' ?>>
                                <option value="">Zgjedh...</option>
                                <?php foreach (options($db, $parts[1], $parts[2]) as $opt): ?>
                                    <option value="<?= e($opt['id']) ?>" <?= (string)$v === (string)$opt['id'] ? 'selected' : '' ?>>
                                        <?= e($opt['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (str_starts_with($type, 'enum:')): ?>
                            <select id="<?= e($key) ?>" name="<?= e($key) ?>">
                                <?php foreach (explode('|', substr($type, 5)) as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= (string)$v === (string)$opt ? 'selected' : '' ?>>
                                        <?= e($opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input id="<?= e($key) ?>"
                                name="<?= e($key) ?>"
                                type="<?= ($type === 'integer' || $type === 'decimal') ? 'number' : ($type === 'date' ? 'date' : 'text') ?>"
                                <?= $type === 'decimal' ? 'step="0.01" min="0"' : ($type === 'integer' ? 'step="1" min="0"' : '') ?>
                                value="<?= e($v) ?>"
                                <?= $f[2] ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Butonat e formularit -->
            <div class="form-actions">
                <button class="btn primary" type="submit"
                    data-loading="<?= $action === 'edit' ? 'Duke ruajtur...' : 'Duke shtuar...' ?>">
                    <i class="bx bx-save"></i>
                    <?= $action === 'edit' ? 'Ruaj ndryshimet' : 'Shto' ?>
                </button>
                <a class="btn ghost" href="?entity=<?= e($entity) ?>">
                    Anulo
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php page_bottom(); ?>