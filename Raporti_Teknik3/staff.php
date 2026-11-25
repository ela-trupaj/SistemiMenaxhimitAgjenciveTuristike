<?php
declare(strict_types=1);

require 'includes/bootstrap.php';
require_role([ROLE_STAFF]);
require 'includes/layout.php';

$user = current_user();

$stmt = $db->query("
    SELECT
        r.id,
        u.emri AS klienti,
        o.titulli AS oferta,
        r.numri_personave,
        r.statusi
    FROM rezervimet r
    INNER JOIN perdoruesit u ON u.id = r.perdoruesi_id
    INNER JOIN ofertat o ON o.id = r.oferta_id
    ORDER BY r.id DESC
    LIMIT 5
");

$rezervimetFundit = $stmt->fetchAll();

page_top('Përmbledhja');
?>

<div class="section-intro">
    <div>
        <div class="eyebrow dark">
            LUNÉA Voyages / STAFI
        </div>

        <h1>
            Mirë se erdhe, <?= e($user['emri']) ?>!
        </h1>

        <p class="muted">
            Paneli i Stafit për menaxhimin e ofertave turistike,
            rezervimeve dhe pagesave.
        </p>
    </div>
</div>

<div class="panel staff-services">
    <div class="panel-head">
        <h2>Menaxhimi i shërbimeve</h2>
    </div>

    <div class="staff-actions">
        <a class="btn primary" href="manage.php?entity=ofertat&action=create">
            <i class="bx bx-plus"></i>
            Shto ofertë
        </a>

        <a class="btn primary" href="manage.php?entity=rezervimet&action=create">
            <i class="bx bx-plus"></i>
            Shto rezervim
        </a>

        <a class="btn ghost" href="manage.php?entity=pagesat&action=create">
            <i class="bx bx-credit-card"></i>
            Regjistro pagesë
        </a>

        <a class="btn ghost" href="manage.php?entity=destinacionet">
            <i class="bx bx-map-pin"></i>
            Shiko destinacionet
        </a>
    </div>
</div>

<div class="panel staff-reservations">
    <div class="panel-head">
        <h2>Rezervimet e fundit</h2>
    </div>

    <?php if ($rezervimetFundit): ?>

        <div class="table-wrap desktop-reservations">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Klienti</th>
                        <th>Oferta</th>
                        <th>Numri i personave</th>
                        <th>Statusi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($rezervimetFundit as $rezervim): ?>
                        <tr>
                            <td>#<?= e($rezervim['id']) ?></td>
                            <td><?= e($rezervim['klienti']) ?></td>
                            <td><?= e($rezervim['oferta']) ?></td>
                            <td><?= e($rezervim['numri_personave']) ?></td>
                            <td>
                                <span class="status">
                                    <?= e($rezervim['statusi']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mobile-reservations">
            <?php foreach ($rezervimetFundit as $rezervim): ?>
                <article class="reservation-card">
                    <div class="reservation-card-head">
                        <strong>
                            Rezervimi #<?= e($rezervim['id']) ?>
                        </strong>

                        <span class="status">
                            <?= e($rezervim['statusi']) ?>
                        </span>
                    </div>

                    <div class="reservation-card-body">
                        <div class="reservation-detail">
                            <span>Klienti</span>
                            <strong><?= e($rezervim['klienti']) ?></strong>
                        </div>

                        <div class="reservation-detail">
                            <span>Oferta</span>
                            <strong><?= e($rezervim['oferta']) ?></strong>
                        </div>

                        <div class="reservation-detail">
                            <span>Numri i personave</span>
                            <strong><?= e($rezervim['numri_personave']) ?></strong>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    <?php else: ?>

        <div class="dashboard-empty">
            <div class="dashboard-empty-icon">
                <i class="bx bx-calendar-x"></i>
            </div>

            <h3>Nuk ka ende rezervime</h3>

            <p>Rezervimet e reja do të shfaqen këtu.</p>
        </div>

    <?php endif; ?>
</div>

<?php page_bottom(); ?>
