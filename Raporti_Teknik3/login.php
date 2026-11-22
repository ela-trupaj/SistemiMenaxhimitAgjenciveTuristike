<?php
declare(strict_types=1);

require 'includes/bootstrap.php';

function redirect_by_role(array $user): never
{
    switch ((int) ($user['roli_id'] ?? 0)) {
        case ROLE_ADMIN:
            redirect('dashboard.php');

        case ROLE_STAFF:
            redirect('staff.php');

        case ROLE_CLIENT:
            redirect('client.php');

        default:
            http_response_code(403);
            exit('Roli i përdoruesit nuk është i vlefshëm.');
    }
}

if ($loggedUser = current_user()) {
    redirect_by_role($loggedUser);
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Ju lutem plotësoni email-in dhe fjalëkalimin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email adresa nuk është e vlefshme.';
    } else {
        try {
            $stmt = $db->prepare(
                'SELECT id, emri, email, fjalekalimi, roli_id
                 FROM perdoruesit
                 WHERE LOWER(email) = ?
                 LIMIT 1'
            );

            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $user &&
                password_verify($password, (string) $user['fjalekalimi'])
            ) {
                session_regenerate_id(true);

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

                    <div class="auth-heading">
                        <h1>Mirë se u ktheve!</h1>
                        <p>Kyçu për të vazhduar në sistem dhe për të eksploruar botën me ne.</p>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="auth-error" role="alert">
                            <i class="bx bx-error-circle"></i>
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="auth-form">

                        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

                        <div class="field">
                            <label for="email">Email adresa</label>

                            <div class="input-wrap">
                                <i class="bx bx-envelope"></i>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Shkruaj email-in"
                                    value="<?= e($email) ?>"
                                    maxlength="190"
                                    autocomplete="username"
                                    required
                                >
                            </div>
                        </div>

                        <div class="field">
                            <label for="password">Fjalëkalimi</label>

                            <div class="input-wrap">
                                <i class="bx bx-lock-alt"></i>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Shkruaj fjalëkalimin"
                                    autocomplete="current-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    aria-label="Shfaq fjalëkalimin"
                                    aria-pressed="false"
                                >
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">
                            Kyçu
                        </button>

                    </form>

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

    <footer>
        <div class="footer-bottom">
            <p>
                © <span id="year"></span> LUNÉA Voyages.
                Të gjitha të drejtat e rezervuara.
            </p>
        </div>
    </footer>

    <script src="assets/auth.js"></script>

</body>
</html>
