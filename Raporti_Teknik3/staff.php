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
    INNER JOIN perdoruesit u
        ON u.id = r.perdoruesi_id
    INNER JOIN ofertat o
        ON o.id = r.oferta_id
    ORDER BY r.id DESC
    LIMIT 5
");

$rezervimetFundit = $stmt->fetchAll();

page_top('Përmbledhja');

?>

<div class="section-intro">

    <div>

        <div class="eyebrow dark">
            NEXORA / STAFI
        </div>

        <h1>
            Mirë se erdhe, <?= e($user['emri']) ?>!
        </h1>

        <p class="muted">
            Paneli i Stafit për menaxhimin e
            ofertave turistike, rezervimeve dhe pagesave.
        </p>

    </div>

</div>

<div class="panel" style="padding: 25px;">

    <div class="panel-head">
        <h2>Menaxhimi i shërbimeve</h2>
    </div>

    <div style="
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 20px;
    ">

        <a
            class="btn primary"
            href="manage.php?entity=ofertat&action=create"
        >
            + Shto ofertë
        </a>

        <a
            class="btn primary"
            href="manage.php?entity=rezervimet&action=create"
        >
            + Shto rezervim
        </a>

        <a
            class="btn ghost"
            href="manage.php?entity=pagesat&action=create"
        >
            + Regjistro pagesë
        </a>

        <a
            class="btn ghost"
            href="manage.php?entity=destinacionet"
        >
            Shiko destinacionet
        </a>

    </div>

</div>

<div class="panel" style="margin-top: 25px;">

    <div class="panel-head">
        <h2>Rezervimet e fundit</h2>
    </div>

    <div class="table-wrap">

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

                        <td>
                            #<?= e($rezervim['id']) ?>
                        </td>

                        <td>
                            <?= e($rezervim['klienti']) ?>
                        </td>

                        <td>
                            <?= e($rezervim['oferta']) ?>
                        </td>

                        <td>
                            <?= e($rezervim['numri_personave']) ?>
                        </td>

                        <td>
                            <?= e($rezervim['statusi']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$rezervimetFundit): ?>

                    <tr>
                        <td colspan="5" class="empty">
                            Nuk ka ende rezervime.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php page_bottom(); ?>
