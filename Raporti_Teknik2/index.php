<?php

declare(strict_types=1);

require 'includes/bootstrap.php';

// ==========================================
// NEXORA - LOGIN
// ==========================================

/**Ridrejton perdoruesin sipas rolit.*/
function redirect_by_role(array $user): never
{
    $roleId = (int) ($user['roli_id'] ?? 0);

    switch ($roleId) {
        case ROLE_ADMIN:
            redirect('dashboard.php');

        case ROLE_STAFF:
            redirect('staff.php');

        case ROLE_CLIENT:
            redirect('client.php');

        default:
            http_response_code(403);
            exit('Roli i perdoruesit nuk eshte i vlefshem.');
    }
}

// Nese perdoruesi eshte i kycur.
if (current_user()) {
    redirect_by_role(current_user());
}

$error = '';
$email = '';

// ==========================================
// PROCESIMI I LOGIN
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Plotesoni email-in dhe fjalekalimin.';
    } else {

        $stmt = $db->prepare(
            'SELECT id, emri, email, fjalekalimi, roli_id
             FROM perdoruesit
             WHERE email = ?
             LIMIT 1'
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (
            $user &&
            password_verify($password, $user['fjalekalimi'])
        ) {

            session_regenerate_id(true);

            unset($user['fjalekalimi']);

            $_SESSION['user'] = $user;

            redirect_by_role($user);
        }

        $error = 'Email ose fjalekalim i pasakte.';
    }
}

?>

<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXORA · Kyçu</title>
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
                Udhëtime të bukura.<br>
                <em>Menaxhim më i lehtë.</em>
            </h1>

            <p>
                Destinacione, oferta dhe rezervime —
                gjithçka në një vend.
            </p>

        </div>

        <div class="login-foot">
            SISTEMI I MENAXHIMIT TË AGJENCIVE TURISTIKE
        </div>

    </section>

    <!-- FORMULARI LOGIN -->
    <section class="login-panel">

        <div class="login-card">

            <div class="mobile-brand">
                NEXORA.
            </div>

            <span class="tag">
                PORTALI NEXORA
            </span>

            <h2>Mirë se u ktheve!</h2>

            <p class="muted">
                Kyçu për të vazhduar në sistem.
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

                <label for="email">
                    Email adresa
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Shkruaj email-in"
                    value="<?= e($email) ?>"
                    autocomplete="username"
                    required
                >

                <label for="password">
                    Fjalëkalimi
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Shkruaj fjalëkalimin"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="submit"
                    class="btn primary full"
                >
                    Kyçu në sistem &nbsp; →
                </button>

            </form>

            <div class="login-hint">
                Nuk ke llogari?
                <a href="register.php">
                    Regjistrohu këtu
                </a>
            </div>

        </div>

    </section>

</div>

</body>
</html>
