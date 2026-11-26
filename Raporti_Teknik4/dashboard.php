<?php
declare(strict_types=1);

require 'includes/bootstrap.php';
require_admin();
require 'includes/layout.php';

$counts = [];

$tables = [
    'destinacionet',
    'ofertat',
    'rezervimet',
    'pagesat'
];

foreach ($tables as $table) {
    $stmt = $db->query('SELECT COUNT(*) FROM ' . $table);
    $counts[$table] = (int) $stmt->fetchColumn();
}

$stmt = $db->query(
    'SELECT
        r.id,
        r.numri_personave,
        r.statusi,
        r.data_rezervimit,
        u.emri AS perdoruesi,
        o.titulli AS oferta
     FROM rezervimet r
     JOIN perdoruesit u ON u.id = r.perdoruesi_id
     JOIN ofertat o ON o.id = r.oferta_id
     ORDER BY r.id DESC
     LIMIT 6'
);

$latest = $stmt->fetchAll();

$user = current_user();
$firstName = explode(' ', $user['emri'])[0];

$stats = [
    ['destinacionet', 'Destinacione', 'bx-map-pin', 'Gjithsej destinacione'],
    ['ofertat', 'Oferta turistike', 'bx-purchase-tag-alt', 'Gjithsej oferta'],
    ['rezervimet', 'Rezervime', 'bx-calendar-check', 'Gjithsej rezervime'],
    ['pagesat', 'Pagesa', 'bx-credit-card', 'Gjithsej pagesa']
];

page_top('Përmbledhja');
?>

<div class="section-intro">
    <div>
        <div class="eyebrow dark">LUNÉA Voyages / PËRMBLEDHJE</div>

        <h1>
            Mirë se erdhe, <?= e($firstName) ?>
        </h1>

        <p class="muted"> Menaxho dhe monitoro aktivitetin e agjencisë turistike.</p>
    </div>

    <div class="dashboard-actions">
        <a class="btn primary" href="manage.php?entity=rezervimet&action=create">
            <i class="bx bx-plus"></i>
            Rezervim i ri
        </a>

        <a class="btn primary" href="create_staff.php">
            <i class="bx bx-user-plus"></i>
            Shto Staf
        </a>
    </div>
</div>

<div class="stats">
    <?php foreach ($stats as $stat): ?>
        <?php
        $key = $stat[0];
        $label = $stat[1];
        $icon = $stat[2];
        $description = $stat[3];
        ?>
        <div class="stat">
            <div class="stat-top">
                <span><?= e($label) ?></span>
                <span class="stat-icon">
                    <i class="bx <?= e($icon) ?>"></i>
                </span>
            </div>
            <strong><?= e($counts[$key]) ?></strong>
            <small><?= e($description) ?></small>
        </div>
    <?php endforeach; ?>
</div>

<div class="panel reservations-panel">
    <div class="panel-head">
        <div>
            <h2>Rezervimet e fundit</h2>
            <p class="muted">Pasqyra e rezervimeve më të fundit të klientëve.</p>
        </div>

        <a class="text-link" href="manage.php?entity=rezervimet">
            Shiko të gjitha
            <i class="bx bx-right-arrow-alt"></i>
        </a>
    </div>

    <?php if ($latest): ?>

        <div class="table-wrap desktop-reservations">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Përdoruesi</th>
                        <th>Oferta</th>
                        <th>Persona</th>
                        <th>Data</th>
                        <th>Statusi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($latest as $reservation): ?>
                        <tr>
                            <td>#<?= e($reservation['id']) ?></td>
                            <td><?= e($reservation['perdoruesi']) ?></td>
                            <td><?= e($reservation['oferta']) ?></td>
                            <td><?= e($reservation['numri_personave']) ?></td>
                            <td><?= e($reservation['data_rezervimit']) ?></td>
                            <td>
                                <span class="status">
                                    <?= e($reservation['statusi']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mobile-reservations">
            <?php foreach ($latest as $reservation): ?>
                <article class="reservation-card">
                    <div class="reservation-card-head">
                        <strong>
                            Rezervimi #<?= e($reservation['id']) ?>
                        </strong>

                        <span class="status">
                            <?= e($reservation['statusi']) ?>
                        </span>
                    </div>

                    <div class="reservation-card-body">
                        <div class="reservation-detail">
                            <span>Përdoruesi</span>
                            <strong><?= e($reservation['perdoruesi']) ?></strong>
                        </div>

                        <div class="reservation-detail">
                            <span>Oferta</span>
                            <strong><?= e($reservation['oferta']) ?></strong>
                        </div>

                        <div class="reservation-detail">
                            <span>Persona</span>
                            <strong><?= e($reservation['numri_personave']) ?></strong>
                        </div>

                        <div class="reservation-detail">
                            <span>Data</span>
                            <strong><?= e($reservation['data_rezervimit']) ?></strong>
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
