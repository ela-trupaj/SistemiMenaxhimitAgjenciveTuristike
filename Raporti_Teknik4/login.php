<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

// Ridrejtimi sipas rolit te perdoruesit
function redirect_by_role(array $user): never
{
    switch ((int)$user['roli_id']) {
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

// Kontrollimi i sesionit ekzistues
if ($loggedUser = current_user()) {
    redirect_by_role($loggedUser);
}

$error = '';
$email = '';

// Procesi i autentikimit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    // Validimi i te dhenave
    if ($email === '' || $password === '') {
        $error = 'Ju lutem plotësoni email-in dhe fjalëkalimin.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email adresa nuk është e vlefshme.';

    } else {
        try {
            // Kerkimi i perdoruesit ne databaze
            $stmt = $db->prepare("
                SELECT id, emri, mbiemri, email, fjalekalimi, roli_id
                FROM perdoruesit
                WHERE LOWER(email) = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verifikimi i fjalekalimit
            if ($user && password_verify($password, $user['fjalekalimi'])) {

                session_regenerate_id(true);

                // Ruajtja e te dhenave ne sesion
                unset($user['fjalekalimi']);
                $_SESSION['user'] = $user;

                redirect_by_role($user);
            }

            $error = 'Email ose fjalëkalim i pasaktë.';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Ndodhi një gabim gjatë kyçjes. Provoni përsëri.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LUNÉA Voyages | Kyçu</title>

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

            <!-- Formulari i kycjes -->
            <section class="auth-panel">
                <div class="auth-content">

                    <!-- Logo -->
                    <a href="index.php" class="auth-logo">
                        <strong>LUNÉA</strong>
                        <span>VOYAGES</span>
                    </a>

                    <!-- Titulli -->
                    <div class="auth-heading">
                        <h1>Mirë se u ktheve!</h1>
                        <p>Kyçu për të vazhduar në sistem dhe për të eksploruar botën me ne.</p>
                    </div>

                    <!-- Mesazhi i gabimit -->
                    <?php if ($error !== ''): ?>

                        <div class="auth-error" role="alert">
                            <i class="bx bx-error-circle"></i>
                            <?= e($error) ?>
                        </div>

                    <?php endif; ?>

                    <!-- Forma e autentikimit -->
                    <form method="POST" class="auth-form">

                        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

                        <!-- Email -->
                        <div class="field">
                            <label for="email">Email adresa</label>

                            <div class="input-wrap">
                                <i class="bx bx-envelope"></i>

                                <input type="email" id="email" name="email" placeholder="Shkruaj email-in" value="<?= e($email) ?>" maxlength="190" autocomplete="username" required>
                            </div>
                        </div>

                        <!-- Fjalekalimi -->
                        <div class="field">
                            <label for="password">Fjalëkalimi</label>

                            <div class="input-wrap">
                                <i class="bx bx-lock-alt"></i>

                                <input type="password" id="password" name="password" placeholder="Shkruaj fjalëkalimin" autocomplete="current-password" required>

                                <button type="button" class="password-toggle" aria-label="Shfaq fjalëkalimin" aria-pressed="false">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Butoni i kycjes -->
                        <button type="submit" class="auth-submit" data-loading="Duke u kyçur...">
                            Kyçu
                        </button>

                    </form>

                    <!-- Regjistrimi -->
                    <div class="auth-separator">
                        <span>Nuk ke llogari?</span>
                    </div>

                    <a href="register.php" class="auth-outline">
                        Regjistrohu këtu
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
    <script src="assets/auth.js?v=2" defer"></script>

</body>
</html>