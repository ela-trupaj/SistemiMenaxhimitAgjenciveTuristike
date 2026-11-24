<?php
declare(strict_types=1);

function page_top(string $title): void
{
    $user = current_user();
    $roleId = (int)($user['roli_id'] ?? 0);

    // Rruga kryesore e projektit
    $baseUrl = BASE_URL;

    $roleName = match ($roleId) {
        ROLE_ADMIN => 'Administrator',
        ROLE_STAFF => 'Staf',
        ROLE_CLIENT => 'Klient',
        default => 'Përdorues'
    };

    $isAdmin = $roleId === ROLE_ADMIN;
    $isStaff = $roleId === ROLE_STAFF;
    $isClient = $roleId === ROLE_CLIENT;

    $panelTitle = match ($roleId) {
        ROLE_ADMIN => 'Paneli i administrimit',
        ROLE_STAFF => 'Paneli i Stafit',
        ROLE_CLIENT => 'Paneli i Klientit',
        default => 'Paneli'
    };

    $homeLink = match ($roleId) {
        ROLE_ADMIN => $baseUrl . 'dashboard.php',
        ROLE_STAFF => $baseUrl . 'staff.php',
        ROLE_CLIENT => $baseUrl . 'client/client.php',
        default => $baseUrl . 'index.php'
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
    <title><?= e($title) ?> · LUNÉA Voyages</title>

    <!-- CSS Link -->
    <link rel="stylesheet" href="<?= e($baseUrl) ?>dashboard.css">

    <!-- BOXICONS Link -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body>

<div class="app">

    <!-- Sidebar -->
    <aside class="sidebar" id="dashboard-sidebar">

        <div class="brand">
            <div>
                <b>LUNÉA Voyages<span class="accent">.</span></b>
                <small>TRAVEL MANAGEMENT</small>
            </div>
        </div>

        <div class="nav-label">
            <?= $isClient ? 'LLOGARIA IME' : 'MENAXHIMI' ?>
        </div>

        <nav>

            <!-- Faqja kryesore sipas rolit -->
            <a href="<?= e($homeLink) ?>">
                <i class="bx bx-grid-alt"></i>
                <span><?= e($homeLabel) ?></span>
            </a>

            <!-- Modulet e Administratorit dhe Stafit -->
            <?php if ($isAdmin || $isStaff): ?>

                <a href="<?= e($baseUrl) ?>manage.php?entity=destinacionet">
                    <i class="bx bx-map-pin"></i>
                    <span>Destinacionet</span>
                </a>

                <a href="<?= e($baseUrl) ?>manage.php?entity=ofertat">
                    <i class="bx bx-purchase-tag-alt"></i>
                    <span>Ofertat turistike</span>
                </a>

                <a href="<?= e($baseUrl) ?>manage.php?entity=rezervimet">
                    <i class="bx bx-calendar-check"></i>
                    <span>Rezervimet</span>
                </a>

                <a href="<?= e($baseUrl) ?>manage.php?entity=pagesat">
                    <i class="bx bx-credit-card"></i>
                    <span>Pagesat</span>
                </a>

            <?php endif; ?>

            <!-- Vetem Administratori -->
            <?php if ($isAdmin): ?>

                <a href="<?= e($baseUrl) ?>create_staff.php">
                    <i class="bx bx-user-plus"></i>
                    <span>Shto Staf</span>
                </a>

            <?php endif; ?>

            <!-- Modulet e Klientit -->
            <?php if ($isClient): ?>

                <a href="<?= e($baseUrl) ?>client/client_offers.php">
                    <i class="bx bx-purchase-tag-alt"></i>
                    <span>Ofertat turistike</span>
                </a>

                <a href="<?= e($baseUrl) ?>client/client_bookings.php">
                    <i class="bx bx-calendar-check"></i>
                    <span>Rezervimet e mia</span>
                </a>

                <a href="<?= e($baseUrl) ?>client/client_payments.php">
                    <i class="bx bx-credit-card"></i>
                    <span>Pagesat e mia</span>
                </a>

                <a href="<?= e($baseUrl) ?>client/client_profile.php">
                    <i class="bx bx-user-circle"></i>
                    <span>Profili im</span>
                </a>

            <?php endif; ?>

        </nav>

        <!-- Te dhenat e perdoruesit -->
        <div class="side-bottom">

            <div class="avatar">
                <?= e(mb_substr($user['emri'] ?? 'U', 0, 1)) ?>
            </div>

            <div>
                <b><?= e($user['emri'] ?? 'Përdorues') ?></b>
                <small><?= e($roleName) ?></small>
            </div>

        </div>

        <!-- Logout per te gjitha rolet -->
        <form method="POST" action="<?= e($baseUrl) ?>logout.php">

            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

            <button type="submit" class="logout">
                Dil nga sistemi
                <i class="bx bx-log-out"></i>
            </button>

        </form>

    </aside>

    <!-- Permbajtja kryesore -->
    <main class="main">

        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Topbar -->
        <header class="topbar">

            <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Hap menynë" aria-controls="dashboard-sidebar" aria-expanded="false">
                <i class="bx bx-menu"></i>
            </button>

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

        <!-- Permbajtja e faqes -->
        <div class="content">

            <!-- Mesazhet e sistemit -->
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
    // Rruga kryesore e projektit
    $baseUrl = BASE_URL;
?>

            <!-- Footer -->
            <footer class="footer">
                © <?= date('Y') ?> LUNÉA Voyages. Të gjitha të drejtat e rezervuara.
            </footer>

        </div>

    </main>

</div>

<!-- JavaScript -->
<script src="<?= e($baseUrl) ?>assets/auth.js"></script>

</body>
</html>

<?php
}