<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require_client();
require __DIR__ . '/../includes/layout.php';

$user = current_user();
$clientId = (int)$user['id'];

$error = '';

// Anulimi i rezervimit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $rezervimiId = (int)($_POST['rezervimi_id'] ?? 0);

    try {
        $stmt = $db->prepare("
            UPDATE rezervimet
            SET statusi = 'Anuluar'
            WHERE id = ?
            AND perdoruesi_id = ?
            AND statusi = 'Ne pritje'
        ");

        $stmt->execute([$rezervimiId, $clientId]);

        if ($stmt->rowCount() > 0) {
            flash('Rezervimi u anulua me sukses.');
            redirect('client_bookings.php');
        } else {
            $error = 'Ky rezervim nuk mund të anulohet.';
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'Anulimi i rezervimit dështoi.';
    }
}

// Marrja e rezervimeve te klientit aktual
$stmt = $db->prepare("
    SELECT
        r.id,
        o.titulli AS oferta,
        d.emri AS destinacioni,
        r.numri_personave,
        r.data_rezervimit,
        o.data_nisjes,
        o.data_kthimit,
        o.cmimi * r.numri_personave AS cmimi_total,
        r.statusi
    FROM rezervimet r
    INNER JOIN ofertat o ON o.id = r.oferta_id
    INNER JOIN destinacionet d ON d.id = o.destinacioni_id
    WHERE r.perdoruesi_id = ?
    ORDER BY r.id DESC
");

$stmt->execute([$clientId]);
$rezervimet = $stmt->fetchAll();

page_top('Rezervimet e mia');
?>

<div class="section-intro">
    <div>
        <div class="eyebrow dark"> LUNÉA Voyages / REZERVIMET </div>
        <h1> Rezervimet e mia </h1>
        <p class="muted">Shiko dhe menaxho rezervimet e udhëtimeve të tua.</p>
    </div>

    <a href="client_offers.php" class="btn primary">
        <i class="bx bx-plus"></i>
        Rezervim i ri
    </a>
</div>

<?php if ($error): ?>
    <div class="notice error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- Historiku i rezervimeve -->
<div class="panel">

    <div class="panel-head">
        <h2>Historiku i rezervimeve</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Oferta</th>
                    <th>Destinacioni</th>
                    <th>Persona</th>
                    <th>Data e rezervimit</th>
                    <th>Nisja</th>
                    <th>Kthimi</th>
                    <th>Çmimi total</th>
                    <th>Statusi</th>
                    <th>Veprimet</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rezervimet as $rezervim): ?>

                    <?php
                    // Percaktimi i statusit te rezervimit
                    $statusClass = match ($rezervim['statusi']) {
                        'Konfirmuar' => 'status-confirmed',
                        'Ne pritje' => 'status-pending',
                        'Anuluar' => 'status-cancelled',
                        default => ''
                    };

                    $statusLabel = match ($rezervim['statusi']) {
                        'Konfirmuar' => 'Konfirmuar',
                        'Ne pritje' => 'Në pritje',
                        'Anuluar' => 'Anuluar',
                        default => 'I panjohur'
                    };
                    ?>

                    <tr>
                        <td>#<?= e($rezervim['id']) ?></td>
                        <td><?= e($rezervim['oferta']) ?></td>
                        <td><?= e($rezervim['destinacioni']) ?></td>
                        <td><?= e($rezervim['numri_personave']) ?></td>

                        <td>
                            <?= $rezervim['data_rezervimit'] ? e(date('d.m.Y', strtotime($rezervim['data_rezervimit']))) : '—' ?>
                        </td>

                        <td>
                            <?= e(date('d.m.Y', strtotime($rezervim['data_nisjes']))) ?>
                        </td>

                        <td>
                            <?= e(date('d.m.Y', strtotime($rezervim['data_kthimit']))) ?>
                        </td>

                        <td>
                            <?= e(number_format((float)$rezervim['cmimi_total'], 2)) ?> €
                        </td>

                        <td>
                            <span class="booking-status <?= e($statusClass) ?>">
                                <?= e($statusLabel) ?>
                            </span>
                        </td>

                        <td>
                            <?php if ($rezervim['statusi'] === 'Ne pritje'): ?>

                                <!-- Anulimi i rezervimit -->
                                <form method="POST" class="cancel-form" onsubmit="return confirm('A jeni të sigurt që dëshironi ta anuloni rezervimin?');">

                                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                    <input type="hidden" name="rezervimi_id" value="<?= e($rezervim['id']) ?>">

                                    <button type="submit" class="btn-cancel">
                                        <i class="bx bx-x"></i>
                                        Anulo
                                    </button>

                                </form>

                            <?php elseif ($rezervim['statusi'] === 'Konfirmuar'): ?>

                                <!-- Shkarkimi i konfirmimit ne PDF -->
                                <a href="../documents/booking_pdf.php?id=<?= e($rezervim['id']) ?>" class="btn btn-pdf" title="Shkarko konfirmimin e rezervimit">
                                    <i class="bx bxs-file-pdf"></i>
                                    PDF
                                </a>

                            <?php else: ?>

                                <span class="muted"> — </span>

                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                <?php if (!$rezervimet): ?>
                    <tr>
                        <td colspan="10" class="empty">
                            Nuk ke ende rezervime.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php page_bottom(); ?>