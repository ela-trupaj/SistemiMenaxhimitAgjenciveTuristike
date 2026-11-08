<?php

declare(strict_types=1);

require 'includes/bootstrap.php';

// Vetem administratori ka qasje.
require_admin();

require 'includes/layout.php';

// ==========================================
// STATISTIKAT E DATABAZES
// ==========================================

$counts = [];

$tables = [
    'destinacionet',
    'ofertat',
    'rezervimet',
    'pagesat'
];

foreach ($tables as $table) {

    $stmt = $db->query(
        'SELECT COUNT(*) FROM ' . $table
    );

    $counts[$table] = (int) $stmt->fetchColumn();
}

// ==========================================
// REZERVIMET E FUNDIT
// ==========================================

$stmt = $db->query(
    'SELECT
        r.id,
        r.numri_personave,
        r.statusi,
        r.data_rezervimit,
        u.emri AS perdoruesi,
        o.titulli AS oferta
     FROM rezervimet r
     JOIN perdoruesit u
        ON u.id = r.perdoruesi_id
     JOIN ofertat o
        ON o.id = r.oferta_id
     ORDER BY r.id DESC
     LIMIT 6'
);

$latest = $stmt->fetchAll();

// ==========================================
// INFORMACIONET E ADMINISTRATORIT
// ==========================================

$user = current_user();

$firstName = explode(' ', $user['emri'])[0];

// ==========================================
// SHFAQJA E DASHBOARD
// ==========================================

page_top('Përmbledhja');

?>

<!-- ===================================== -->
<!-- PERSHENDETJA DHE BUTONAT -->
<!-- ===================================== -->

<div class="section-intro">

    <div>

        <div class="eyebrow dark">
            NEXORA / OVERVIEW
        </div>

        <h1>
            Mirë se erdhe,
            <?= e($firstName) ?>
            <span class="wave">✳</span>
        </h1>

        <p class="muted">
            Pamje e shpejtë e operacioneve
            të agjencisë turistike.
        </p>

    </div>

    <div style="
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    ">

        <a
            class="btn primary"
            href="manage.php?entity=rezervimet&action=create"
        >
            + Rezervim i ri
        </a>

        <a
            class="btn primary"
            href="create_staff.php"
        >
            + Shto Staf
        </a>

    </div>

</div>

<!-- ===================================== -->
<!-- STATISTIKAT -->
<!-- ===================================== -->

<div class="stats">

    <?php

    $stats = [
        ['destinacionet', 'Destinacione', '⌖'],
        ['ofertat', 'Oferta aktive', '▧'],
        ['rezervimet', 'Rezervime', '▤'],
        ['pagesat', 'Pagesa', '◈']
    ];

    foreach ($stats as $stat):

        $key = $stat[0];
        $label = $stat[1];
        $icon = $stat[2];

    ?>

        <div class="stat">

            <div class="stat-top">

                <span>
                    <?= e($label) ?>
                </span>

                <span class="stat-icon">
                    <?= e($icon) ?>
                </span>

            </div>

            <strong>
                <?= e($counts[$key]) ?>
            </strong>

            <small>
                Të regjistruara në databazë
            </small>

        </div>

    <?php endforeach; ?>

</div>

<!-- ===================================== -->
<!-- REZERVIMET E FUNDIT -->
<!-- ===================================== -->

<div class="panel">

    <div class="panel-head">

        <div>

            <h2>
                Rezervimet e fundit
            </h2>

            <p class="muted">
                Të dhëna reale nga MySQL
            </p>

        </div>

        <a
            class="text-link"
            href="manage.php?entity=rezervimet"
        >
            Shiko të gjitha →
        </a>

    </div>

    <div class="table-wrap">

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

                        <td>
                            #<?= e($reservation['id']) ?>
                        </td>

                        <td>
                            <?= e($reservation['perdoruesi']) ?>
                        </td>

                        <td>
                            <?= e($reservation['oferta']) ?>
                        </td>

                        <td>
                            <?= e($reservation['numri_personave']) ?>
                        </td>

                        <td>
                            <?= e($reservation['data_rezervimit']) ?>
                        </td>

                        <td>
                            <span class="status">
                                <?= e($reservation['statusi']) ?>
                            </span>
                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$latest): ?>

                    <tr>
                        <td colspan="6" class="empty">
                            Ende nuk ka rezervime.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php page_bottom(); ?>
