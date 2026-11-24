<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require_client();
require __DIR__ . '/../includes/layout.php';

$user = current_user();
$clientId = (int)$user['id'];

// Marrja e pagesave te klientit aktual
$stmt = $db->prepare("
    SELECT
        p.id,
        p.rezervimi_id,
        o.titulli AS oferta,
        p.shuma,
        p.metoda,
        p.data_pageses,
        p.statusi
    FROM pagesat p
    INNER JOIN rezervimet r ON r.id = p.rezervimi_id
    INNER JOIN ofertat o ON o.id = r.oferta_id
    WHERE r.perdoruesi_id = ?
    ORDER BY p.id DESC
");

$stmt->execute([$clientId]);
$pagesat = $stmt->fetchAll();

// Statistikat e pagesave
$totalPaguar = 0;
$totalNePritje = 0;
$totalDeshtuar = 0;

foreach ($pagesat as $pagesa) {
    switch ($pagesa['statusi']) {
        case 'Paguar':
            $totalPaguar += (float)$pagesa['shuma'];
            break;

        case 'Ne pritje':
            $totalNePritje += (float)$pagesa['shuma'];
            break;

        case 'Deshtuar':
            $totalDeshtuar += (float)$pagesa['shuma'];
            break;
    }
}

page_top('Pagesat e mia');
?>

<div class="section-intro">
    <div>
        <div class="eyebrow dark"> LUNÉA Voyages / PAGESAT </div>
        <h1> Pagesat e mia </h1>
        <p class="muted">Shiko historikun e pagesave dhe menaxho dokumentet e udhëtimeve të tua.</p>
    </div>
</div>

<!-- Statistikat e pagesave -->
<div class="payment-stats">

    <div class="payment-card">
        <div class="payment-card-head">
            <span>Pagesat e kryera</span>
            <i class="bx bx-check-circle"></i>
        </div>

        <h2><?= e(number_format($totalPaguar, 2)) ?> €</h2>
        <p class="muted">Pagesat e konfirmuara</p>
    </div>

    <div class="payment-card">
        <div class="payment-card-head">
            <span>Pagesat në pritje</span>
            <i class="bx bx-time-five"></i>
        </div>

        <h2><?= e(number_format($totalNePritje, 2)) ?> €</h2>
        <p class="muted">Në pritje të konfirmimit</p>
    </div>

    <div class="payment-card">
        <div class="payment-card-head">
            <span>Pagesat e dështuara</span>
            <i class="bx bx-error-circle"></i>
        </div>

        <h2><?= e(number_format($totalDeshtuar, 2)) ?> €</h2>
        <p class="muted">Pagesat e parealizuara</p>
    </div>

</div>

<!-- Historiku i pagesave -->
<div class="panel">

    <div class="panel-head">
        <h2>Historiku i pagesave</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Rezervimi</th>
                    <th>Oferta</th>
                    <th>Shuma</th>
                    <th>Metoda</th>
                    <th>Data e pagesës</th>
                    <th>Statusi</th>
                    <th>Dokumenti</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($pagesat as $pagesa): ?>

                    <?php
                    $statusClass = match ($pagesa['statusi']) {
                        'Paguar' => 'status-confirmed',
                        'Ne pritje' => 'status-pending',
                        'Deshtuar' => 'status-cancelled',
                        default => ''
                    };

                    $statusLabel = match ($pagesa['statusi']) {
                        'Paguar' => 'Paguar',
                        'Ne pritje' => 'Në pritje',
                        'Deshtuar' => 'Dështuar',
                        default => 'I panjohur'
                    };
                    ?>

                    <tr>
                        <td>#<?= e($pagesa['id']) ?></td>
                        <td>#<?= e($pagesa['rezervimi_id']) ?></td>
                        <td><?= e($pagesa['oferta']) ?></td>
                        <td><?= e(number_format((float)$pagesa['shuma'], 2)) ?> €</td>
                        <td><?= e($pagesa['metoda']) ?></td>

                        <td>
                            <?= $pagesa['data_pageses'] ? e(date('d.m.Y', strtotime($pagesa['data_pageses']))) : '—' ?>
                        </td>

                        <td>
                            <span class="booking-status <?= e($statusClass) ?>">
                                <?= e($statusLabel) ?>
                            </span>
                        </td>

                        <!-- Shkarkimi i deshmise se pageses -->
                        <td>
                            <?php if ($pagesa['statusi'] === 'Paguar'): ?>

                                <a href="../documents/payment_pdf.php?id=<?= e($pagesa['id']) ?>" class="btn btn-pdf" title="Shkarko dëshminë e pagesës">                                    <i class="bx bxs-file-pdf"></i>
                                    PDF
                                </a>

                            <?php else: ?>

                                <span class="muted"> — </span>

                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                <?php if (!$pagesat): ?>
                    <tr>
                        <td colspan="8" class="empty">
                            Nuk ke ende pagesa të regjistruara.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php page_bottom(); ?>