<?php

declare(strict_types=1);

require 'includes/bootstrap.php';

// Vetem administratori mund ta hape kete faqe.
require_admin();

require 'includes/layout.php';

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name = trim((string) ($_POST['emri'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['fjalekalimi'] ?? '');
    $confirmPassword = (string) ($_POST['konfirmo_fjalekalimin'] ?? '');

    // Validimi i te dhenave
    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Emri duhet te kete nga 1 deri ne 100 karaktere.';
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 190
    ) {
        $errors[] = 'Shkruaj nje email te vlefshem.';
    }

    if (strlen($password) < 12) {
        $errors[] = 'Fjalekalimi duhet te kete te pakten 12 karaktere.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Fjalekalimet nuk perputhen.';
    }

    // Kontrollo nese email-i ekziston
    if (!$errors) {
        $stmt = $db->prepare(
            'SELECT id FROM perdoruesit WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);

        if ($stmt->fetchColumn()) {
            $errors[] = 'Ky email eshte i regjistruar.';
        }
    }

    // Regjistro perdoruesin me rolin Staf
    if (!$errors) {
        try {
            $stmt = $db->prepare(
                'INSERT INTO perdoruesit
                 (emri, email, fjalekalimi, roli_id)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                ROLE_STAFF
            ]);

            flash('success', 'Llogaria e stafit u krijua me sukses!');
            redirect('dashboard.php');

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Llogaria nuk u krijua. Kontrollo te dhenat dhe provo perseri.';
        }
    }
}

page_top('Shto Staf');

?>

<div class="section-intro">
    <div>
        <div class="eyebrow dark">NEXORA / STAFI</div>
        <h1>Shto anëtar të stafit</h1>
        <p class="muted">
            Krijo një llogari të re për punonjësit e agjencisë.
        </p>
    </div>

    <a class="btn" href="dashboard.php">
        ← Kthehu te Dashboard
    </a>
</div>

<div class="panel" style="max-width: 650px; padding: 30px;">

    <h2>Të dhënat e stafit</h2>

    <p class="muted">
        Plotëso formularin për të krijuar një llogari të re.
    </p>

    <?php if ($errors): ?>
        <div
            role="alert"
            style="
                background: #fff0f0;
                color: #9b2020;
                padding: 15px;
                border-radius: 10px;
                margin: 20px 0;
            "
        >
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="create_staff.php">

        <input
            type="hidden"
            name="_csrf"
            value="<?= e(csrf()) ?>"
        >

        <div style="margin: 20px 0;">
            <label for="emri">Emri dhe mbiemri</label>
            <input
                id="emri"
                type="text"
                name="emri"
                value="<?= e($name) ?>"
                placeholder="P.sh. Staf NEXORA"
                maxlength="100"
                required
                style="width: 100%; padding: 12px; margin-top: 8px;"
            >
        </div>

        <div style="margin: 20px 0;">
            <label for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="<?= e($email) ?>"
                placeholder="staf@nexora.local"
                maxlength="190"
                required
                style="width: 100%; padding: 12px; margin-top: 8px;"
            >
        </div>

        <div style="margin: 20px 0;">
            <label for="fjalekalimi">Fjalëkalimi</label>
            <input
                id="fjalekalimi"
                type="password"
                name="fjalekalimi"
                placeholder="Minimum 12 karaktere"
                minlength="12"
                autocomplete="new-password"
                required
                style="width: 100%; padding: 12px; margin-top: 8px;"
            >
        </div>

        <div style="margin: 20px 0;">
            <label for="konfirmo_fjalekalimin">
                Konfirmo fjalëkalimin
            </label>
            <input
                id="konfirmo_fjalekalimin"
                type="password"
                name="konfirmo_fjalekalimin"
                placeholder="Përsërit fjalëkalimin"
                minlength="12"
                autocomplete="new-password"
                required
                style="width: 100%; padding: 12px; margin-top: 8px;"
            >
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <button class="btn primary" type="submit">
                + Krijo llogarinë
            </button>

            <a class="btn" href="dashboard.php">
                Anulo
            </a>
        </div>

    </form>

</div>

<?php page_bottom(); ?>
