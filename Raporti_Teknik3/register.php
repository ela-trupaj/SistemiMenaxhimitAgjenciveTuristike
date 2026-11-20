<?php
declare(strict_types=1);
require 'includes/bootstrap.php';

if ($user = current_user()) {
    switch ((int)($user['roli_id'] ?? 0)) {
        case ROLE_ADMIN: redirect('dashboard.php');
        case ROLE_STAFF: redirect('staff.php');
        case ROLE_CLIENT: redirect('client.php');
        default:
            http_response_code(403);
            exit('Roli i përdoruesit nuk është i vlefshëm.');
    }
}

$error = '';
$emri = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $emri = trim((string)($_POST['emri'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if ($emri === '' || $email === '' ||
        $password === '' || $confirmPassword === '') {
        $error = 'Ju lutem plotësoni të gjitha fushat.';
    } elseif (mb_strlen($emri) > 100) {
        $error = 'Emri nuk mund të ketë më shumë se 100 karaktere.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
              strlen($email) > 190) {
        $error = 'Email adresa nuk është e vlefshme.';
    } elseif (strlen($password) < 8) {
        $error = 'Fjalëkalimi duhet të ketë të paktën 8 karaktere.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Fjalëkalimet nuk përputhen.';
    } else {
        $stmt = $db->prepare(
            'SELECT id FROM perdoruesit WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Ky email është regjistruar më parë.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $db->prepare(
                    'INSERT INTO perdoruesit
                     (emri, email, fjalekalimi, roli_id)
                     VALUES (?, ?, ?, ?)'
                );

                $stmt->execute([
                    $emri, $email, $passwordHash, ROLE_CLIENT
                ]);

                flash('Regjistrimi u krye me sukses. Tani mund të kyçeni.');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>LUNÉA Voyages | Regjistrohu</title>

    <link rel="stylesheet" href="assets/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body class="auth-body">

    <a href="index.php" class="back-home">
        <i class="bx bx-home-alt"></i>
        Kthehu në Ballinë
    </a>
    
<main class="auth-shell">
    <div class="auth-container">

        <section class="auth-visual" aria-label="Pamje nga destinacionet">
            <div class="visual-overlay"></div>
        </section>

        <section class="auth-panel">
            <div class="auth-content">

                <a href="index.php" class="auth-logo">
                    <strong>LUNÉA</strong>
                    <span>VOYAGES</span>
                </a>

                <div class="auth-heading register-heading">
                    <h1>Krijo llogarinë tënde</h1>
                    <p>Bëhu pjesë e LUNÉA Voyages dhe fillo planifikimin e udhëtimit tënd.</p>
                </div>

                <?php if ($error): ?>
                    <div class="auth-error" role="alert">
                        <i class="bx bx-error-circle"></i>
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="auth-form">
                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

                    <div class="field">
                        <label for="emri">Emri dhe mbiemri</label>
                        <div class="input-wrap">
                            <i class="bx bx-user"></i>
                            <input type="text" id="emri" name="emri" placeholder="Shkruaj emrin dhe mbiemrin" value="<?= e($emri) ?>" maxlength="100" autocomplete="name" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="email">Email adresa</label>
                        <div class="input-wrap">
                            <i class="bx bx-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Shkruaj email-in" value="<?= e($email) ?>" maxlength="190" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Fjalëkalimi</label>
                        <div class="input-wrap">
                            <i class="bx bx-lock-alt"></i>
                            <input type="password" id="password" name="password" placeholder="Minimumi 8 karaktere" minlength="8" autocomplete="new-password" required>
                            <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label for="confirm_password">Konfirmo fjalëkalimin</label>
                        <div class="input-wrap">
                            <i class="bx bx-lock-alt"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Përsërit fjalëkalimin" autocomplete="new-password" required>
                            <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit"> Regjistrohu </button>
                </form>

                <div class="auth-separator">
                    <span>Ke tashmë llogari?</span>
                </div>

                <a href="index.php" class="auth-outline"> Kyçu këtu </a>
            </div>
        </section>
    </div>
</main>

<script src="assets/auth.js"></script>
</body>
</html>
