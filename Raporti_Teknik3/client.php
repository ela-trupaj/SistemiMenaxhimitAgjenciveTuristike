<?php

declare(strict_types=1);

require 'includes/bootstrap.php';

require_client();

require 'includes/layout.php';

$user = current_user();
$clientId = (int) $user['id'];

// Ofertat turistike te fundit.
$stmt = $db->query("
    SELECT
        o.id,
        o.titulli,
        d.emri AS destinacioni,
        o.cmimi,
        o.data_nisjes,
        o.data_kthimit,
        o.vende
    FROM ofertat o
    INNER JOIN destinacionet d
        ON d.id = o.destinacioni_id
    ORDER BY o.id DESC
    LIMIT 6
");

$ofertat = $stmt->fetchAll();

// Rezervimet vetem te klientit aktual.
$stmt = $db->prepare("
    SELECT
        r.id,
        o.titulli AS oferta,
        r.numri_personave,
        r.statusi
    FROM rezervimet r
    INNER JOIN ofertat o
        ON o.id = r.oferta_id
    WHERE r.perdoruesi_id = ?
    ORDER BY r.id DESC
    LIMIT 5
");

$stmt->execute([$clientId]);
$rezervimet = $stmt->fetchAll();

page_top('Ballina');

?>

<div class="section-intro">

    <div>

        <div class="eyebrow dark">
            NEXORA / KLIENTI
        </div>

        <h1>
            Mirë se erdhe, <?= e($user['emri']) ?>!
        </h1>

        <p class="muted">
            Zbulo destinacione të reja, eksploro ofertat
            turistike dhe planifiko udhëtimin tënd.
        </p>

    </div>

</div>

<div class="panel" style="margin-bottom: 25px;">

    <div class="panel-head">
        <h2>Ofertat turistike</h2>
    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>Oferta</th>
                    <th>Destinacioni</th>
                    <th>Çmimi</th>
                    <th>Nisja</th>
                    <th>Kthimi</th>
                    <th>Vendet</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($ofertat as $oferta): ?>

                    <tr>

                        <td><?= e($oferta['titulli']) ?></td>

                        <td><?= e($oferta['destinacioni']) ?></td>

                        <td><?= e($oferta['cmimi']) ?> €</td>

                        <td><?= e($oferta['data_nisjes']) ?></td>

                        <td><?= e($oferta['data_kthimit']) ?></td>

                        <td><?= e($oferta['vende']) ?></td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$ofertat): ?>

                    <tr>
                        <td colspan="6" class="empty">
                            Nuk ka ende oferta turistike.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<div class="panel">

    <div class="panel-head">
        <h2>Rezervimet e mia</h2>
    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Oferta</th>
                    <th>Numri i personave</th>
                    <th>Statusi</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($rezervimet as $rezervim): ?>

                    <tr>

                        <td>#<?= e($rezervim['id']) ?></td>

                        <td><?= e($rezervim['oferta']) ?></td>

                        <td><?= e($rezervim['numri_personave']) ?></td>

                        <td><?= e($rezervim['statusi']) ?></td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$rezervimet): ?>

                    <tr>
                        <td colspan="4" class="empty">
                            Nuk ke ende rezervime.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php page_bottom(); ?>
