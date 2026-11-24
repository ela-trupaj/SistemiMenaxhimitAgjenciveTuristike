<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require_client();
require __DIR__ . '/../includes/layout.php';

$user = current_user();
$clientId = (int)$user['id'];

$error = '';

// Marrja e te dhenave te klientit
$stmt = $db->prepare("
    SELECT id, emri, mbiemri, email, fjalekalimi
    FROM perdoruesit
    WHERE id = ?
");

$stmt->execute([$clientId]);
$klienti = $stmt->fetch();

if (!$klienti) {
    http_response_code(404);
    exit('Përdoruesi nuk u gjet.');
}

$emri = $klienti['emri'];
$mbiemri = $klienti['mbiemri'];
$email = $klienti['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = (string)($_POST['action'] ?? '');

    // Ndryshimi i te dhenave personale
    if ($action === 'update_profile') {

        $emri = trim((string)($_POST['emri'] ?? ''));
        $mbiemri = trim((string)($_POST['mbiemri'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));

        if ($emri === '' || $mbiemri === '' || $email === '') {
            $error = 'Ju lutem plotësoni të gjitha fushat.';

        } elseif (mb_strlen($emri) > 100 || mb_strlen($mbiemri) > 100) {
            $error = 'Emri dhe mbiemri nuk duhet të kalojnë 100 karaktere.';

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $error = 'Email adresa nuk është e vlefshme.';

        } else {

            // Kontrollimi i email-it
            $stmt = $db->prepare("
                SELECT id
                FROM perdoruesit
                WHERE email = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([$email, $clientId]);

            if ($stmt->fetch()) {
                $error = 'Ky email përdoret nga një llogari tjetër.';

            } else {
                try {
                    $stmt = $db->prepare("
                        UPDATE perdoruesit
                        SET emri = ?, mbiemri = ?, email = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $emri,
                        $mbiemri,
                        $email,
                        $clientId
                    ]);

                    // Perditesimi i sesionit
                    $_SESSION['user']['emri'] = $emri;
                    $_SESSION['user']['mbiemri'] = $mbiemri;
                    $_SESSION['user']['email'] = $email;

                    flash('Të dhënat personale u përditësuan me sukses.');
                    redirect('client_profile.php');

                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $error = 'Përditësimi i profilit dështoi.';
                }
            }
        }
    }

    // Ndryshimi i fjalekalimit
    if ($action === 'update_password') {

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'Ju lutem plotësoni të gjitha fushat.';

        } elseif (!password_verify($currentPassword, $klienti['fjalekalimi'])) {
            $error = 'Fjalëkalimi aktual është i pasaktë.';

        } elseif (strlen($newPassword) < 8) {
            $error = 'Fjalëkalimi duhet të ketë të paktën 8 karaktere.';

        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Fjalëkalimet nuk përputhen.';

        } elseif (password_verify($newPassword, $klienti['fjalekalimi'])) {
            $error = 'Fjalëkalimi i ri duhet të jetë ndryshe nga ai aktual.';

        } else {
            try {
                $stmt = $db->prepare("
                    UPDATE perdoruesit
                    SET fjalekalimi = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    password_hash($newPassword, PASSWORD_DEFAULT),
                    $clientId
                ]);

                session_regenerate_id(true);

                flash('Fjalëkalimi u ndryshua me sukses.');
                redirect('client_profile.php');

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Ndryshimi i fjalëkalimit dështoi.';
            }
        }
    }
}

page_top('Profili im');
?>

<div class="section-intro">
    <div>
        <div class="eyebrow dark"> LUNÉA Voyages / PROFILI </div>
        <h1> Profili im </h1>
        <p class="muted">Menaxho të dhënat personale dhe sigurinë e llogarisë tënde.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="notice error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- Informacionet e profilit -->
<div class="panel profile-header">

    <div class="profile-avatar">
        <?= e(mb_strtoupper(mb_substr($emri, 0, 1))) ?>
    </div>

    <div>
        <h2><?= e($emri . ' ' . $mbiemri) ?></h2>
        <p class="muted"><?= e($email) ?></p>
        <span class="badge">Klient</span>
    </div>

</div>

<div class="profile-grid">

    <!-- Te dhenat personale -->
    <div class="panel">

        <div class="panel-head">
            <h2>Të dhënat personale</h2>
        </div>

        <p class="muted">Përditëso informacionet e llogarisë tënde.</p>

        <form method="POST" class="profile-form">

            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <input type="hidden" name="action" value="update_profile">

            <div class="profile-field">
                <label for="emri">Emri</label>
                <input type="text" id="emri" name="emri" value="<?= e($emri) ?>" maxlength="100" required>
            </div>

            <div class="profile-field">
                <label for="mbiemri">Mbiemri</label>
                <input type="text" id="mbiemri" name="mbiemri" value="<?= e($mbiemri) ?>" maxlength="100" required>
            </div>

            <div class="profile-field">
                <label for="email">Email adresa</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="190" required>
            </div>

            <button type="submit" class="btn primary">
                <i class="bx bx-save"></i>
                Ruaj ndryshimet
            </button>

        </form>

    </div>

    <!-- Siguria e llogarise -->
    <div class="panel">

        <div class="panel-head">
            <h2>Siguria e llogarisë</h2>
        </div>

        <p class="muted">Ndrysho fjalëkalimin për të mbrojtur llogarinë tënde.</p>

        <form method="POST" class="profile-form">

            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <input type="hidden" name="action" value="update_password">

            <div class="profile-field">
                <label for="current_password">Fjalëkalimi aktual</label>

                <div class="profile-password">
                    <input type="password" id="current_password" name="current_password" placeholder="Fjalëkalimi aktual" autocomplete="current-password" required>

                    <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                        <i class="bx bx-show"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <label for="new_password">Fjalëkalimi i ri</label>

                <div class="profile-password">
                    <input type="password" id="new_password" name="new_password" placeholder="Minimumi 8 karaktere" minlength="8" autocomplete="new-password" required>

                    <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                        <i class="bx bx-show"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <label for="confirm_password">Konfirmo fjalëkalimin</label>

                <div class="profile-password">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Përsërit fjalëkalimin" minlength="8" autocomplete="new-password" required>

                    <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                        <i class="bx bx-show"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn primary">
                <i class="bx bx-lock-alt"></i>
                Ndrysho fjalëkalimin
            </button>

        </form>

    </div>

</div>

<?php page_bottom(); ?>