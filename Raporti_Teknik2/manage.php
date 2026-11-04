<?php

declare(strict_types=1);

require 'includes/bootstrap.php';

require_staff();

require 'includes/entities.php';
require 'includes/layout.php';

// ==========================================
// MODULI DHE VEPRIMI AKTUAL
// ==========================================

$entity = (string) ($_GET['entity'] ?? '');
$action = (string) ($_GET['action'] ?? 'list');

$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
) ?: null;

if (!isset($entities[$entity])) {
    http_response_code(404);
    exit('Moduli nuk ekziston.');
}

if (!in_array($action, ['list', 'create', 'edit'], true)) {
    http_response_code(404);
    exit('Veprim i panjohur.');
}

$meta = $entities[$entity];

$user = current_user();
$roleId = (int) ($user['roli_id'] ?? 0);

$isAdmin = $roleId === ROLE_ADMIN;
$isStaff = $roleId === ROLE_STAFF;

// ==========================================
// AUTORIZIMET SIPAS ROLEVE
// ==========================================

// Veprimet qe lejohen per Stafin.
$staffPermissions = [
    'destinacionet' => ['list'],
    'ofertat' => ['list', 'create', 'edit'],
    'rezervimet' => ['list', 'create', 'edit'],
    'pagesat' => ['list', 'create']
];

function can_manage(
    string $entity,
    string $action,
    bool $isAdmin,
    array $staffPermissions
): bool {

    // Administratori ka qasje te plote.
    if ($isAdmin) {
        return in_array(
            $action,
            ['list', 'create', 'edit', 'delete'],
            true
        );
    }

    // Stafi ka qasje vetem sipas listes.
    return in_array(
        $action,
        $staffPermissions[$entity] ?? [],
        true
    );
}

if (!can_manage(
    $entity,
    $action,
    $isAdmin,
    $staffPermissions
)) {
    http_response_code(403);
    exit('Nuk keni autorizim per kete veprim.');
}

// ==========================================
// PERGATITJA E TE DHENAVE
// ==========================================

$errors = [];
$record = [];
$rows = [];

// ==========================================
// POST - CREATE / UPDATE / DELETE
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $operation = (string) ($_POST['operation'] ?? '');

    // --------------------------------------
    // DELETE
    // --------------------------------------

    if ($operation === 'delete') {

        if (!can_manage(
            $entity,
            'delete',
            $isAdmin,
            $staffPermissions
        )) {
            http_response_code(403);
            exit('Nuk keni autorizim per fshirje.');
        }

        if (!$id) {
            http_response_code(400);
            exit('ID e pavlefshme.');
        }

        try {

            $stmt = $db->prepare(
                'DELETE FROM ' . $entity . ' WHERE id = ?'
            );

            $stmt->execute([$id]);

            flash(
                $stmt->rowCount()
                    ? 'Regjistri u fshi.'
                    : 'Regjistri nuk ekziston.'
            );

        } catch (PDOException $e) {

            flash(
                'Nuk mund të fshihet: regjistri mund të jetë i lidhur me të dhëna të tjera.',
                'error'
            );
        }

        redirect('manage.php?entity=' . $entity);
    }

    // --------------------------------------
    // CREATE / UPDATE
    // --------------------------------------

    if ($operation === 'save') {

        // Percakto nese kerkesa eshte CREATE ose EDIT.
        if ($action === 'create') {
            $requestedAction = 'create';

        } elseif ($action === 'edit') {
            $requestedAction = 'edit';

        } else {
            http_response_code(400);
            exit('Veprim i pavlefshem per ruajtje.');
        }

        // Kontrollo autorizimin ne backend.
        if (!can_manage(
            $entity,
            $requestedAction,
            $isAdmin,
            $staffPermissions
        )) {
            http_response_code(403);
            exit('Nuk keni autorizim per ruajtje.');
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

            redirect('manage.php?entity=' . $entity);
        }

    } elseif ($operation !== 'delete') {

        http_response_code(400);
        exit('Operacion i panjohur.');
    }
}

// ==========================================
// MARRJA E REGJISTRIT PER EDITIM
// ==========================================

if ($action === 'edit') {

    if (!$id) {
        http_response_code(400);
        exit('ID e pavlefshme.');
    }

    $stmt = $db->prepare(
        'SELECT * FROM ' . $entity . ' WHERE id = ?'
    );

    $stmt->execute([$id]);

    $record = $stmt->fetch();

    if (!$record) {
        http_response_code(404);
        exit('Regjistri nuk ekziston.');
    }
}

// ==========================================
// LISTIMI I REGJISTRAVE
// ==========================================

if ($action === 'list') {

    $rows = $db->query($meta['list'])->fetchAll();
}

// ==========================================
// SHFAQJA E FAQES
// ==========================================

page_top($meta['title']);

?>

<!-- HEADER -->

<div class="section-intro">

    <div>

        <div class="eyebrow dark">
            NEXORA / MENAXHIMI
        </div>

        <h1>
            <?= e($meta['title']) ?>
        </h1>

        <p class="muted">
            Menaxho të dhënat me ruajtje reale në MySQL.
        </p>

    </div>

    <?php if ($action === 'list'): ?>

        <?php if (can_manage(
            $entity,
            'create',
            $isAdmin,
            $staffPermissions
        )): ?>

            <a
                class="btn primary"
                href="?entity=<?= e($entity) ?>&action=create"
            >
                + Shto të re
            </a>

        <?php endif; ?>

    <?php else: ?>

        <a
            class="btn ghost"
            href="?entity=<?= e($entity) ?>"
        >
            Kthehu
        </a>

    <?php endif; ?>

</div>

<!-- LISTIMI -->
<?php if ($action === 'list'): ?>

    <div class="panel">

        <div class="panel-head">

            <h2>
                Regjistrat
                <span class="count">
                    <?= count($rows) ?>
                </span>
            </h2>

        </div>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>

                        <th>ID</th>

                        <?php foreach ($meta['fields'] as $key => $f): ?>
                            <th><?= e($f[0]) ?></th>
                        <?php endforeach; ?>

                        <?php
                        $canEdit = can_manage(
                            $entity,
                            'edit',
                            $isAdmin,
                            $staffPermissions
                        );

                        $canDelete = can_manage(
                            $entity,
                            'delete',
                            $isAdmin,
                            $staffPermissions
                        );
                        ?>

                        <?php if ($canEdit || $canDelete): ?>
                            <th>Veprime</th>
                        <?php endif; ?>

                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <td>#<?= e($r['id']) ?></td>

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

                                <td><?= e($display) ?></td>

                            <?php endforeach; ?>

                            <?php if ($canEdit || $canDelete): ?>

                                <td class="actions">

                                    <?php if ($canEdit): ?>

                                        <a
                                            href="?entity=<?= e($entity) ?>&action=edit&id=<?= e($r['id']) ?>"
                                        >
                                            Ndrysho
                                        </a>

                                    <?php endif; ?>

                                    <?php if ($canDelete): ?>

                                        <form
                                            method="POST"
                                            action="?entity=<?= e($entity) ?>&id=<?= e($r['id']) ?>"
                                            onsubmit="return confirm('A jeni të sigurt që doni ta fshini?')"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf"
                                                value="<?= e(csrf()) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="operation"
                                                value="delete"
                                            >

                                            <button
                                                class="danger-link"
                                                type="submit"
                                            >
                                                Fshi
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </td>

                            <?php endif; ?>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$rows): ?>

                        <tr>
                            <td
                                class="empty"
                                colspan="<?= count($meta['fields']) + (($canEdit || $canDelete) ? 2 : 1) ?>"
                            >
                                Nuk ka ende të dhëna.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<!-- CREATE / EDIT -->
<?php elseif (in_array($action, ['create', 'edit'], true)): ?>

    <div class="panel form-panel">

        <div class="panel-head">

            <h2>
                <?= $action === 'edit'
                    ? 'Ndrysho regjistrin'
                    : 'Shto regjistër të ri' ?>
            </h2>

        </div>

        <?php if ($errors): ?>

            <div class="notice error" role="alert">
                <?= e(implode(' ', $errors)) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            class="entity-form"
            novalidate
        >

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf()) ?>"
            >

            <input
                type="hidden"
                name="operation"
                value="save"
            >

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

                            <textarea
                                id="<?= e($key) ?>"
                                name="<?= e($key) ?>"
                                rows="4"
                            ><?= e($v) ?></textarea>

                        <?php elseif (str_starts_with($type, 'select:')): ?>

                            <?php $parts = explode(':', $type); ?>

                            <select
                                id="<?= e($key) ?>"
                                name="<?= e($key) ?>"
                                <?= $f[2] ? 'required' : '' ?>
                            >

                                <option value="">
                                    Zgjedh...
                                </option>

                                <?php foreach (
                                    options($db, $parts[1], $parts[2])
                                    as $opt
                                ): ?>

                                    <option
                                        value="<?= e($opt['id']) ?>"
                                        <?= (string) $v === (string) $opt['id']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= e($opt['label']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        <?php elseif (str_starts_with($type, 'enum:')): ?>

                            <select
                                id="<?= e($key) ?>"
                                name="<?= e($key) ?>"
                            >

                                <?php foreach (
                                    explode('|', substr($type, 5))
                                    as $opt
                                ): ?>

                                    <option
                                        value="<?= e($opt) ?>"
                                        <?= (string) $v === (string) $opt
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= e($opt) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        <?php else: ?>

                            <input
                                id="<?= e($key) ?>"
                                name="<?= e($key) ?>"
                                type="<?= ($type === 'integer' || $type === 'decimal')
                                    ? 'number'
                                    : ($type === 'date' ? 'date' : 'text') ?>"
                                <?= $type === 'decimal'
                                    ? 'step="0.01" min="0"'
                                    : ($type === 'integer'
                                        ? 'step="1" min="0"'
                                        : '') ?>
                                value="<?= e($v) ?>"
                                <?= $f[2] ? 'required' : '' ?>
                            >

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <div class="form-actions">

                <button
                    class="btn primary"
                    type="submit"
                >
                    <?= $action === 'edit'
                        ? 'Ruaj ndryshimet'
                        : 'Shto regjistrin' ?>
                </button>

                <a
                    class="btn ghost"
                    href="?entity=<?= e($entity) ?>"
                >
                    Anulo
                </a>

            </div>

        </form>

    </div>

<?php endif; ?>

<?php page_bottom(); ?>
