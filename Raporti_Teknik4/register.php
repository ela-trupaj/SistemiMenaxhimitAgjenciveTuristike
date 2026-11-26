<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

// Ridrejtimi sipas rolit te perdoruesit
if ($user = current_user()) {
    switch ((int)($user['roli_id'] ?? 0)) {
        case ROLE_ADMIN:
            redirect(BASE_URL . 'dashboard.php');

        case ROLE_STAFF:
            redirect(BASE_URL . 'staff.php');

        case ROLE_CLIENT:
            redirect(BASE_URL . 'client/client.php');

        default:
            http_response_code(403);
            exit('Roli i përdoruesit nuk është i vlefshëm.');
    }
}

$error = '';
$emri = '';
$email = '';

// Procesi i regjistrimit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $emri = trim((string)($_POST['emri'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    // Ndarja e emrit dhe mbiemrit
    $nameParts = preg_split('/\s+/u', $emri, 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';

    // Validimi i te dhenave
    if ($emri === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'Ju lutem plotësoni të gjitha fushat.';

    } elseif ($firstName === '' || $lastName === '') {
        $error = 'Ju lutem shkruani emrin dhe mbiemrin.';

    } elseif (mb_strlen($emri) > 100) {
        $error = 'Emri dhe mbiemri nuk mund të kenë më shumë se 100 karaktere.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $error = 'Email adresa nuk është e vlefshme.';

    } elseif (strlen($password) < 8) {
        $error = 'Fjalëkalimi duhet të ketë të paktën 8 karaktere.';

    } elseif (strlen($password) > 72) {
        $error = 'Fjalëkalimi nuk mund të kalojë 72 bajte.';

    } elseif ($password !== $confirmPassword) {
        $error = 'Fjalëkalimet nuk përputhen.';

    } else {
        try {
            // Kontrollimi i email-it ne databaze
            $stmt = $db->prepare("
                SELECT id
                FROM perdoruesit
                WHERE LOWER(email) = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = 'Ky email është regjistruar më parë.';

            } else {
                // Enkriptimi i fjalekalimit
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Regjistrimi i klientit ne databaze
                $stmt = $db->prepare("
                    INSERT INTO perdoruesit
                    (emri, mbiemri, email, fjalekalimi, roli_id)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $firstName,
                    $lastName,
                    $email,
                    $passwordHash,
                    ROLE_CLIENT
                ]);

                // Mesazhi i suksesit dhe ridrejtimi
                flash('Regjistrimi u krye me sukses. Tani mund të kyçeni.');
                redirect(BASE_URL . 'login.php');
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());

            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                $error = 'Ky email është regjistruar më parë.';

            } else {
                $error = 'Regjistrimi dështoi. Provoni përsëri.';
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

    <!-- CSS Link -->
    <link rel="stylesheet" href="assets/auth.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Boxicons Link -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body class="auth-body">

    <!-- Kthimi ne balline -->
    <a href="index.php" class="back-home">
        <i class="bx bx-home-alt"></i>
        Kthehu në Ballinë
    </a>

    <main class="auth-shell">
        <div class="auth-container">

            <!-- Pjesa vizuale -->
            <section class="auth-visual" aria-label="Pamje nga destinacionet">
                <div class="visual-overlay"></div>
            </section>

            <!-- Formulari i regjistrimit -->
            <section class="auth-panel">
                <div class="auth-content">

                    <!-- Logo -->
                    <a href="index.php" class="auth-logo">
                        <strong>LUNÉA</strong>
                        <span>VOYAGES</span>
                    </a>

                    <!-- Titulli -->
                    <div class="auth-heading register-heading">
                        <h1>Krijo llogarinë tënde</h1>
                        <p>Bëhu pjesë e LUNÉA Voyages dhe fillo planifikimin e udhëtimit tënd.</p>
                    </div>

                    <!-- Mesazhi i gabimit -->
                    <?php if ($error !== ''): ?>

                        <div class="auth-error" role="alert">
                            <i class="bx bx-error-circle"></i>
                            <?= e($error) ?>
                        </div>

                    <?php endif; ?>

                    <!-- Forma e regjistrimit -->
                    <form method="POST" class="auth-form">

                        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

                        <!-- Emri dhe mbiemri -->
                        <div class="field">
                            <label for="emri">Emri dhe mbiemri</label>

                            <div class="input-wrap">
                                <i class="bx bx-user"></i>

                                <input type="text" id="emri" name="emri" placeholder="Shkruaj emrin dhe mbiemrin" value="<?= e($emri) ?>" maxlength="100" autocomplete="name" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="field">
                            <label for="email">Email adresa</label>

                            <div class="input-wrap">
                                <i class="bx bx-envelope"></i>

                                <input type="email" id="email" name="email" placeholder="Shkruaj email-in" value="<?= e($email) ?>" maxlength="190" autocomplete="email" required>
                            </div>
                        </div>

                        <!-- Fjalekalimi -->
                        <div class="field">
                            <label for="password">Fjalëkalimi</label>

                            <div class="input-wrap">
                                <i class="bx bx-lock-alt"></i>

                                <input type="password" id="password" name="password" placeholder="Minimumi 8 karaktere" minlength="8" maxlength="72" autocomplete="new-password" required>

                                <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Konfirmimi i fjalekalimit -->
                        <div class="field">
                            <label for="confirm_password">Konfirmo fjalëkalimin</label>

                            <div class="input-wrap">
                                <i class="bx bx-lock-alt"></i>

                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Përsërit fjalëkalimin" minlength="8" maxlength="72" autocomplete="new-password" required>

                                <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Butoni i regjistrimit -->
                        <button type="submit" class="auth-submit"
                                data-loading="Duke u regjistruar...">
                                Regjistrohu
                        </button>

                    </form>

                    <!-- Kycja -->
                    <div class="auth-separator">
                        <span>Ke tashmë llogari?</span>
                    </div>

                    <a href="login.php" class="auth-outline">
                        Kyçu këtu
                    </a>

                </div>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-bottom">
            <p>© <span id="year"></span> LUNÉA Voyages. Të gjitha të drejtat e rezervuara.</p>
        </div>
    </footer>

    <!-- JavaScript Link -->
    <script src="assets/auth.js"></script>

</body>
</html>