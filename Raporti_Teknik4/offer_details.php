<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_staff();

require __DIR__ . '/includes/layout.php';

// Marrja e ID-se se ofertes
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if (!$id) {
    http_response_code(400);
    exit('ID e pavlefshme.');
}

// Marrja e detajeve te ofertes
$stmt = $db->prepare(
    'SELECT o.*, d.emri AS destinacioni, d.shteti
     FROM ofertat o
     JOIN destinacionet d ON d.id = o.destinacioni_id
     WHERE o.id = ?'
);

$stmt->execute([$id]);
$offer = $stmt->fetch();

if (!$offer) {
    http_response_code(404);
    exit('Oferta nuk ekziston.');
}

// Kontrollimi i statusit
$isActive = $offer['statusi'] === 'Aktive';

page_top('Detajet e ofertës');
?>

<!-- Titulli i faqes -->
<div class="section-intro">
    <div>
        <div class="eyebrow dark">
            LUNÉA Voyages / OFERTAT TURISTIKE
        </div>

        <h1>Detajet e ofertës</h1>
        <p class="muted"> Informacionet e plota të paketës turistike. </p>
    </div>

    <a class="btn ghost" href="manage.php?entity=ofertat">
        <i class="bx bx-arrow-back"></i>
        Kthehu
    </a>
</div>

<!-- Detajet kryesore te ofertes -->
<div class="panel offer-details">

    <div class="offer-details-header">

        <div class="offer-details-image">
            <?php if (!empty($offer['foto'])): ?>
                <img
                    src="<?= e(BASE_URL . $offer['foto']) ?>"
                    alt="<?= e($offer['titulli']) ?>"
                >
            <?php else: ?>
                <div class="offer-details-placeholder">
                    <i class="bx bx-image"></i>
                    <span>Pa fotografi</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="offer-details-info">

            <span class="badge <?= $isActive ? 'success' : 'error' ?>">
                <?= e($offer['statusi']) ?>
            </span>

            <h2><?= e($offer['titulli']) ?></h2>

            <p class="offer-location">
                <i class="bx bx-map"></i>
                <?= e($offer['destinacioni']) ?>,
                <?= e($offer['shteti']) ?>
            </p>

            <div class="offer-price">
                <?= number_format((float)$offer['cmimi'], 2, ',', '.') ?> €
                <span>/ person</span>
            </div>

            <a class="btn primary"
                href="manage.php?entity=ofertat&action=edit&id=<?= e($offer['id']) ?>">
                <i class="bx bx-edit-alt"></i>
                Ndrysho ofertën
            </a>
        </div>
    </div>

    <!-- Informacionet e udhetimit -->
    <div class="offer-details-grid">

        <div class="offer-detail-item">
            <i class="bx bx-calendar"></i>
            <div>
                <span>Data e nisjes</span>
                <strong>
                    <?= date('d.m.Y', strtotime($offer['data_nisjes'])) ?>
                </strong>
            </div>
        </div>

        <div class="offer-detail-item">
            <i class="bx bx-calendar-check"></i>
            <div>
                <span>Data e kthimit</span>
                <strong>
                    <?= date('d.m.Y', strtotime($offer['data_kthimit'])) ?>
                </strong>
            </div>
        </div>

        <div class="offer-detail-item">
            <i class="bx bx-group"></i>
            <div>
                <span>Kapaciteti</span>
                <strong><?= e($offer['kapaciteti']) ?> persona</strong>
            </div>
        </div>

        <div class="offer-detail-item">
            <i class="bx bx-trip"></i>
            <div>
                <span>Transporti</span>
                <strong><?= e($offer['transporti'] ?: '—') ?></strong>
            </div>
        </div>

        <div class="offer-detail-item">
            <i class="bx bx-map-pin"></i>
            <div>
                <span>Vendi i nisjes</span>
                <strong><?= e($offer['vendi_nisjes'] ?: '—') ?></strong>
            </div>
        </div>

        <div class="offer-detail-item">
            <i class="bx bx-building-house"></i>
            <div>
                <span>Akomodimi</span>
                <strong><?= e($offer['akomodimi'] ?: '—') ?></strong>
            </div>
        </div>

    </div>

    <!-- Pershkrimi i ofertes -->
    <div class="offer-details-section">
        <h3>Përshkrimi i ofertës</h3>

        <p> <?= nl2br(e($offer['pershkrimi'] ?: 'Nuk ka përshkrim.')) ?> </p>
    </div>

    <!-- Permbajtja e paketes -->
<div class="offer-package-grid">

    <div class="offer-package included">
        <h3> Paketa përfshin </h3>

        <?php if (!empty(trim($offer['paketa_perfshin'] ?? ''))): ?>

            <ul class="package-list">
                <?php foreach (preg_split('/\R/', $offer['paketa_perfshin']) as $item): ?>
                    <?php if (trim($item) !== ''): ?>
                        <li>
                            <i class="bx bx-check"></i>
                            <span><?= e(trim($item)) ?></span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

        <?php else: ?>
            <p class="muted">Nuk ka informacione.</p>
        <?php endif; ?>

    </div>

    <div class="offer-package excluded">
        <h3> Paketa nuk përfshin </h3>

        <?php if (!empty(trim($offer['paketa_nuk_perfshin'] ?? ''))): ?>

            <ul class="package-list">
                <?php foreach (preg_split('/\R/', $offer['paketa_nuk_perfshin']) as $item): ?>
                    <?php if (trim($item) !== ''): ?>
                        <li>
                            <i class="bx bx-x"></i>
                            <span><?= e(trim($item)) ?></span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

        <?php else: ?>
            <p class="muted">Nuk ka informacione.</p>
        <?php endif; ?>

    </div>

</div>
</div>
<?php page_bottom(); ?>