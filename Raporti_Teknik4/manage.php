<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_staff();

require __DIR__ . '/includes/entities.php';
require __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/upload.php';

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
// Fushat që shfaqen në tabelën e regjistrave
$listFields = $meta['fields'];

if ($entity === 'ofertat') {
    $visibleFields = [
        'titulli',
        'cmimi',
        'kapaciteti',
        'data_nisjes',
        'data_kthimit',
        'statusi'
    ];

    $listFields = [];

    foreach ($visibleFields as $field) {
        if (isset($meta['fields'][$field])) {
            $listFields[$field] = $meta['fields'][$field];
        }
    }
}
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

        $input = $_POST;
        unset($input['foto']);
        $newPhoto = null;

        // Ngarkimi i fotografive për destinacionet dhe ofertat
        if (in_array($entity, ['destinacionet', 'ofertat'], true)) {
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $newPhoto = $entity === 'destinacionet'
                        ? upload_destination_image($_FILES['foto'])
                        : upload_offer_image($_FILES['foto']);

                    $input['foto'] = $newPhoto;
                } catch (RuntimeException $e) {
                    $errors['foto'] = $e->getMessage();
                }
            }
        }

        // Ruajtja në databazë
        if (!$errors) {
            $errors = persist_entity(
                $db,
                $entity,
                $meta['fields'],
                $input,
                $requestedAction === 'edit' ? $id : null,
                true
            );
        }

        // Largo fotografinë e re nëse ruajtja dështon
        if ($errors && $newPhoto !== null) {
            $path = __DIR__ . '/' . $newPhoto;

            if (is_file($path)) {
                unlink($path);
            }
        }

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
                <table class="<?= $entity === 'ofertat' ? 'offers-table' : '' ?>">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php foreach ($listFields as $key => $f): ?>
                                <th><?= $entity === 'ofertat' && $key === 'titulli' ? 'Oferta' : e($f[0]) ?></th>
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
                                <?php foreach ($listFields as $key => $f): ?>
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
                                    <?php if ($entity === 'ofertat' && $key === 'titulli'): ?>
                                        <div class="offer-info">
                                            <?php if (!empty($r['foto'])): ?>
                                                <img src="<?= e(BASE_URL . $r['foto']) ?>" alt="Fotografia e ofertës" class="destination-thumbnail" loading="lazy">
                                            <?php else: ?>
                                                <div class="offer-placeholder"><i class="bx bx-image"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= e($display) ?></strong>
                                                <span class="muted"><?= e($r['destinacioni'] ?? '') ?></span>
                                            </div>
                                        </div>
                                    <?php elseif ($entity === 'ofertat' && $key === 'statusi'): ?>
                                        <span class="offer-status <?= $display === 'Aktive' ? 'is-active' : 'is-inactive' ?>"><?= e($display) ?></span>
                                    <?php elseif ($entity === 'ofertat' && $key === 'cmimi'): ?>
                                        <?= e(number_format((float)$display, 2)) ?> €
                                    <?php elseif ($f[1] === 'file'): ?>
                                    <?php if (!empty($display)): ?>
                                    <img src="<?= e(BASE_URL . $display) ?>" alt="Fotografia e regjistrit" class="destination-thumbnail" loading="lazy">
                                    <?php else: ?>
                                    <span class="muted"> Pa fotografi </span>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <?= e($display) ?>
                                    <?php endif; ?>
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
                                        <?php if ($entity === 'ofertat'): ?>
                                        <a href="offer_details.php?id=<?= e($r['id']) ?>">
                                            <i class="bx bx-show"></i>
                                            Detajet
                                        </a>
                                <?php endif; ?>
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
        <div class="notice error form-notice" role="alert" <?= !$errors ? 'hidden' : '' ?>>
            <i class="bx bx-error-circle"></i>
            <div>
                <strong>Formulari nuk është ruajtur.</strong>
                <p><?= e($errors['general'] ?? 'Kontrolloni fushat e shënuara dhe provoni përsëri.') ?></p>
            </div>
        </div>
        <form method="POST" class="entity-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <input type="hidden" name="operation" value="save">
            <div class="form-grid">
                <?php foreach ($meta['fields'] as $key => $f): ?>
                    <?php
                    $v = $_POST[$key] ?? $record[$key] ?? '';
                    $type = $f[1];
                    $fieldError = $errors[$key] ?? '';
                    ?>
                    <div class="field <?= in_array($type, ['textarea', 'file'], true) ? 'wide' : '' ?> <?= $fieldError ? 'has-error' : '' ?>">
                    <label for="<?= e($key) ?>">
                            <?= e($f[0]) ?>
                            <?= $f[2] ? '*' : '' ?>
                        </label>
                        <?php if ($type === 'file'): ?>
                            <div class="image-upload">
                                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" class="image-input">

                                <label for="foto" class="upload-area" id="upload-area">
                                    <span class="upload-icon"><i class="bx bx-image-add"></i></span>
                                    <strong>Ngarko fotografinë</strong>
                                    <span class="upload-description">Tërhiq fotografinë këtu ose zgjidhe nga pajisja.</span>
                                    <span class="upload-button"><i class="bx bx-upload"></i> Zgjidh fotografinë</span>
                                    <small>JPG, PNG ose WEBP · Maksimumi 5 MB</small>
                                </label>

                                <p id="upload-error" class="upload-error" role="alert" hidden></p>
                                <img id="image-preview" class="image-preview"
                                    <?php if (!empty($record['foto'])): ?>
                                        src="<?= e(BASE_URL . $record['foto']) ?>"
                                    <?php else: ?>
                                        hidden
                                    <?php endif; ?>
                                    alt="Pamja paraprake e fotografisë">
                                <p id="image-name" class="image-name" aria-live="polite"></p>
                            </div>
                        <?php elseif ($type === 'textarea'): ?>

                            <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="4" <?= $f[2] ? 'required' : '' ?>><?= e($v) ?></textarea>
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
                            <select id="<?= e($key) ?>" name="<?= e($key) ?>" <?= $f[2] ? 'required' : '' ?>>
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
                                <?= $type === 'decimal' ? 'step="0.01" min="0"' : ($type === 'integer' ? 'step="1" min="' . ($key === 'numri_personave' ? '1' : '0') . '"' : '') ?>
                                value="<?= e($v) ?>"
                                <?= $f[2] ? 'required' : '' ?>>
                        <?php endif; ?>
                        <p class="field-error" id="error-<?= e($key) ?>" <?= !$fieldError ? 'hidden' : '' ?>><?= e($fieldError) ?></p>
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

<script>
const imageInput = document.getElementById('foto');
const imagePreview = document.getElementById('image-preview');
const uploadArea = document.getElementById('upload-area');
const imageName = document.getElementById('image-name');
const uploadError = document.getElementById('upload-error');

let previewUrl = null;

if (imageInput && imagePreview && uploadArea && imageName && uploadError) {
    const originalImage = imagePreview.getAttribute('src');

    // Validimi i fotografise
    function validateImage(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        uploadError.hidden = true;

        if (!allowedTypes.includes(file.type)) {
            uploadError.textContent = 'Lejohen vetëm fotografitë JPG, PNG dhe WEBP.';
            uploadError.hidden = false;
            return false;
        }
        if (file.size > 5 * 1024 * 1024) {
            uploadError.textContent = 'Fotografia nuk duhet të kalojë 5 MB.';
            uploadError.hidden = false;
            return false;
        }
        return true;
    }

    // Rikthimi i fotografise ekzistuese
    function resetPreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
        imageInput.value = '';
        imageName.textContent = '';
        if (originalImage) {
            imagePreview.src = originalImage;
            imagePreview.hidden = false;
        } else {
            imagePreview.removeAttribute('src');
            imagePreview.hidden = true;
        }
    }

    // Paraqitja e fotografise
    function showPreview(file) {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        previewUrl = URL.createObjectURL(file);
        imagePreview.src = previewUrl;
        imagePreview.hidden = false;
        imageName.textContent = 'Fotografia e zgjedhur: ' + file.name;
    }

    // Zgjedhja e fotografise nga pajisja
    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file || !validateImage(file)) {
            resetPreview();
            return;
        }
        showPreview(file);
    });

    // Drag & Drop
    uploadArea.addEventListener('dragover', function (event) {
        event.preventDefault();
        uploadArea.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function () {
        uploadArea.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function (event) {
        event.preventDefault();
        uploadArea.classList.remove('drag-over');
        const file = event.dataTransfer.files[0];
        if (!file) return;
        if (!validateImage(file)) {
            resetPreview();
            return;
        }
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        imageInput.files = dataTransfer.files;
        showPreview(file);
    });
}
// Validimi i formularit
const entityForm = document.querySelector('.entity-form');

if (entityForm) {
    const fields = entityForm.querySelectorAll('.field input:not([type="file"]), .field select, .field textarea');
    const notice = document.querySelector('.form-notice');
    const departure = entityForm.querySelector('#data_nisjes');
    const arrival = entityForm.querySelector('#data_kthimit');

    function validateField(input) {
        const field = input.closest('.field');
        const error = field.querySelector('.field-error');
        let message = '';

        if (input.required && input.value.trim() === '') {
            message = 'Kjo fushë është e detyrueshme.';
        } else if (!input.validity.valid) {
            message = 'Shënoni një vlerë të vlefshme.';
        } else if (input === arrival && departure && departure.value && arrival.value && arrival.value < departure.value) {
            message = 'Data e kthimit nuk mund të jetë para nisjes.';
        }

        field.classList.toggle('has-error', message !== '');
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        input.setAttribute('aria-describedby', error.id);
        error.textContent = message;
        error.hidden = message === '';
        return message === '';
    }

    fields.forEach(function (input) {
        const field = input.closest('.field');
        input.setAttribute('aria-describedby', field.querySelector('.field-error').id);
        input.setAttribute('aria-invalid', field.classList.contains('has-error') ? 'true' : 'false');

        function updateField() {
            if (field.classList.contains('has-error')) {
                validateField(input);
            }
            if (input === departure && arrival && arrival.closest('.field').classList.contains('has-error')) {
                validateField(arrival);
            }
        }

        input.addEventListener('input', updateField);
        input.addEventListener('change', updateField);
    });

    entityForm.addEventListener('submit', function (event) {
        let firstInvalid = null;

        fields.forEach(function (input) {
            if (!validateField(input) && !firstInvalid) {
                firstInvalid = input;
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            event.stopImmediatePropagation();
            notice.querySelector('p').textContent = 'Kontrolloni fushat e shënuara dhe provoni përsëri.';
            notice.hidden = false;
            firstInvalid.focus();
        }
    }, true);
}
</script>

<?php page_bottom(); ?>
