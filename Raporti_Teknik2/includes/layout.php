<?php
declare(strict_types=1);

function page_top(string $title): void
{
    $user = current_user();
    $roleId = (int) ($user['roli_id'] ?? 0);

    $roleName = match ($roleId) {
        ROLE_ADMIN => 'Administrator',
        ROLE_STAFF => 'Staf',
        ROLE_CLIENT => 'Klient',
        default => 'Përdorues'
    };

    $isAdmin = $roleId === ROLE_ADMIN;
    $isStaff = $roleId === ROLE_STAFF;

    $panelTitle = match ($roleId) {
        ROLE_ADMIN => 'Paneli i administrimit',
        ROLE_STAFF => 'Paneli i Stafit',
        ROLE_CLIENT => 'Paneli i Klientit',
        default => 'Paneli'
    };

    $homeLink = match ($roleId) {
        ROLE_ADMIN => 'dashboard.php',
        ROLE_STAFF => 'staff.php',
        ROLE_CLIENT => 'client.php',
        default => 'index.php'
    };

    $homeLabel = match ($roleId) {
        ROLE_ADMIN => 'Përmbledhja',
        ROLE_STAFF => 'Paneli i Stafit',
        ROLE_CLIENT => 'Ballina',
        default => 'Paneli'
    };
    ?>

<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · NEXORA</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="app">

    <aside class="sidebar">

        <div class="brand">

            <div class="brand-mark">N</div>

            <div>
                <b>NEXORA<span class="accent">.</span></b>
                <small>TRAVEL MANAGEMENT</small>
            </div>

        </div>

        <div class="nav-label">
            <?= $roleId === ROLE_CLIENT ? 'LLOGARIA IME' : 'MENAXHIMI' ?>
        </div>

        <nav>

            <!-- Faqja kryesore sipas rolit -->
            <a href="<?= e($homeLink) ?>">
                ◫ &nbsp; <?= e($homeLabel) ?>
            </a>

            <!-- Modulet e Administratorit dhe Stafit -->
            <?php if ($isAdmin || $isStaff): ?>

                <a href="manage.php?entity=destinacionet">
                    ⌖ &nbsp; Destinacionet
                </a>

                <a href="manage.php?entity=ofertat">
                    ▧ &nbsp; Ofertat turistike
                </a>

                <a href="manage.php?entity=rezervimet">
                    ▤ &nbsp; Rezervimet
                </a>

                <a href="manage.php?entity=pagesat">
                    ◈ &nbsp; Pagesat
                </a>

            <?php endif; ?>

            <!-- Vetem Administratori -->
            <?php if ($isAdmin): ?>

                <a href="create_staff.php">
                    + &nbsp; Shto Staf
                </a>

            <?php endif; ?>

        </nav>

        <div class="side-bottom">

            <div class="avatar">
                <?= e(mb_substr($user['emri'] ?? 'U', 0, 1)) ?>
            </div>

            <div>

                <b>
                    <?= e($user['emri'] ?? 'Përdorues') ?>
                </b>

                <small>
                    <?= e($roleName) ?>
                </small>

            </div>

        </div>

        <!-- Logout per te gjitha rolet -->
        <form method="POST" action="logout.php">

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf()) ?>"
            >

            <button type="submit" class="logout">
                Dil nga sistemi ↗
            </button>

        </form>

    </aside>

    <main class="main">

        <header class="topbar">

            <span>
                <?= e($panelTitle) ?>
                <span class="slash">/</span>
                <?= e($title) ?>
            </span>

            <span class="top-pill">
                <span class="dot"></span>
                Sistemi aktiv
            </span>

        </header>

        <div class="content">

            <?php if (isset($_SESSION['flash'])): ?>

                <?php
                [$message, $kind] = $_SESSION['flash'];
                unset($_SESSION['flash']);
                ?>

                <div class="notice <?= e($kind) ?>">
                    <?= e($message) ?>
                </div>

            <?php endif; ?>

    <?php
}

function page_bottom(): void
{
    ?>

            <footer class="footer">
                © 2026 NEXORA · Travel Management System
            </footer>

        </div>

    </main>

</div>

<script src="assets/app.js"></script>

</body>
</html>

<?php
}
