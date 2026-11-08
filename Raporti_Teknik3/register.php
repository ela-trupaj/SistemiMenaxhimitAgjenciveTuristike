<?php

declare(strict_types=1);

require 'includes/bootstrap.php';

// ==========================================
// NEXORA - REGJISTRIMI I KLIENTIT
// ==========================================

if (current_user()) {
    redirect('dashboard.php');
}

$error = '';
$emri = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $emri = trim((string) ($_POST['emri'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    // VALIDIMI
    if ($emri === '' || $email === '' ||
        $password === '' || $confirmPassword === '') {

        $error = 'Ju lutem plotësoni të gjitha fushat.';

    } elseif (mb_strlen($emri) > 100) {

        $error = 'Emri nuk mund të ketë më shumë se 100 karaktere.';

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 190
    ) {

        $error = 'Email adresa nuk është e vlefshme.';

    } elseif (strlen($password) < 8) {

        $error = 'Fjalëkalimi duhet të ketë të paktën 8 karaktere.';

    } elseif ($password !== $confirmPassword) {

        $error = 'Fjalëkalimet nuk përputhen.';

    } else {

        // Kontrollojme nese email-i ekziston.
        $stmt = $db->prepare(
            'SELECT id FROM perdoruesit WHERE email = ? LIMIT 1'
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $error = 'Ky email është regjistruar më parë.';

        } else {

            // Fjalekalimi ruhet i enkriptuar me hash.
            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {

                // Regjistrimi publik krijon vetem klient.
                $stmt = $db->prepare(
                    'INSERT INTO perdoruesit
                     (emri, email, fjalekalimi, roli_id)
                     VALUES (?, ?, ?, ?)'
                );

                $stmt->execute([
                    $emri,
                    $email,
                    $passwordHash,
                    ROLE_CLIENT
                ]);

                flash(
                    'Regjistrimi u krye me sukses. Tani mund të kyçeni.'
                );

                redirect('index.php');

            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {
                    $error = 'Ky email është regjistruar më parë.';
                } else {
                    error_log($e->getMessage());
                    $error = 'Regjistrimi dështoi. Provoni përsëri.';
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>NEXORA · Regjistrohu</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="login-body">

<div class="login-wrap">

    <!-- PJESA VIZUALE -->
    <section class="login-visual">

        <div class="login-brand">
            NEXORA<span>.</span>
        </div>

        <div class="login-message">

            <div class="eyebrow">
                TRAVEL OPERATIONS PLATFORM
            </div>

            <h1>
                Udhëtimi yt fillon këtu.<br>
                <em>Bëhu pjesë e NEXORA.</em>
            </h1>

            <p>
                Krijo llogarinë tënde për të eksploruar
                ofertat dhe për të menaxhuar rezervimet.
            </p>

        </div>

        <div class="login-foot">
            SISTEMI I MENAXHIMIT TË AGJENCIVE TURISTIKE
        </div>

    </section>

    <!-- FORMULARI REGISTER -->
    <section class="login-panel">

        <div class="login-card">

            <div class="mobile-brand">
                NEXORA.
            </div>

            <span class="tag">
                REGJISTRIMI I KLIENTIT
            </span>

            <h2>Krijo llogarinë tënde</h2>

            <p class="muted">
                Plotëso të dhënat për t'u regjistruar.
            </p>

            <?php if ($error): ?>
                <div class="notice error">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= e(csrf()) ?>"
                >

                <label for="emri">
                    Emri dhe mbiemri
                </label>

                <input
                    type="text"
                    id="emri"
                    name="emri"
                    value="<?= e($emri) ?>"
                    placeholder="Shkruaj emrin dhe mbiemrin"
                    maxlength="100"
                    autocomplete="name"
                    required
                >

                <label for="email">
                    Email adresa
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= e($email) ?>"
                    placeholder="Shkruaj email-in"
                    maxlength="190"
                    autocomplete="email"
                    required
                >

                <label for="password">
                    Fjalëkalimi
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimumi 8 karaktere"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <label for="confirm_password">
                    Konfirmo fjalëkalimin
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Përsërit fjalëkalimin"
                    autocomplete="new-password"
                    required
                >

                <button
                    type="submit"
                    class="btn primary full"
                >
                    Regjistrohu &nbsp; →
                </button>

            </form>

            <div class="login-hint">
                Ke tashmë llogari?
                <a href="index.php">Kyçu këtu</a>
            </div>

        </div>

    </section>

</div>

</body>
</html>
