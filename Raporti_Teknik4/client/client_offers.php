<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require_client();
require __DIR__ . '/../includes/layout.php';

$user = current_user();
$clientId = (int)$user['id'];

$error = '';

// Rezervimi i ofertes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $ofertaId = (int)($_POST['oferta_id'] ?? 0);
    $numriPersonave = (int)($_POST['numri_personave'] ?? 0);

    if ($ofertaId <= 0 || $numriPersonave <= 0) {
        $error = 'Ju lutem plotësoni të dhënat saktë.';
    } else {
        try {
            $db->beginTransaction();

            // Kontrollimi i ofertes
            $stmt = $db->prepare("
                SELECT id, kapaciteti
                FROM ofertat
                WHERE id = ?
                AND statusi = 'Aktive'
                AND data_nisjes >= CURDATE()
                FOR UPDATE
            ");

            $stmt->execute([$ofertaId]);
            $oferta = $stmt->fetch();

            if (!$oferta) {
                throw new RuntimeException('Oferta nuk është e disponueshme.');
            }

            // Kontrollimi i vendeve te rezervuara
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(numri_personave), 0)
                FROM rezervimet
                WHERE oferta_id = ?
                AND statusi IN ('Ne pritje', 'Konfirmuar')
            ");

            $stmt->execute([$ofertaId]);

            $vendeRezervuara = (int)$stmt->fetchColumn();
            $vendeLira = (int)$oferta['kapaciteti'] - $vendeRezervuara;

            if ($numriPersonave > $vendeLira) {
                throw new RuntimeException('Nuk ka vende të mjaftueshme për këtë ofertë.');
            }

            // Regjistrimi i rezervimit
            $stmt = $db->prepare("
                INSERT INTO rezervimet
                (perdoruesi_id, oferta_id, numri_personave, statusi)
                VALUES (?, ?, ?, 'Ne pritje')
            ");

            $stmt->execute([
                $clientId,
                $ofertaId,
                $numriPersonave
            ]);

            $db->commit();

            flash('Rezervimi u realizua me sukses! Prisni konfirmimin nga agjencia.');
            redirect('client_offers.php');

        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            error_log($e->getMessage());
            $error = 'Rezervimi dështoi. Provoni përsëri.';

        } catch (RuntimeException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

// Marrja e ofertave aktive
$stmt = $db->query("
    SELECT
        o.id,
        o.titulli,
        o.pershkrimi,
        d.emri AS destinacioni,
        d.shteti,
        o.cmimi,
        o.data_nisjes,
        o.data_kthimit,
        o.kapaciteti,
        GREATEST(
            o.kapaciteti - (
                SELECT COALESCE(SUM(r.numri_personave), 0)
                FROM rezervimet r
                WHERE r.oferta_id = o.id
                AND r.statusi IN ('Ne pritje', 'Konfirmuar')
            ), 0
        ) AS vende_lira
    FROM ofertat o
    INNER JOIN destinacionet d ON d.id = o.destinacioni_id
    WHERE o.statusi = 'Aktive'
    AND o.data_nisjes >= CURDATE()
    ORDER BY o.data_nisjes ASC
");

$ofertat = $stmt->fetchAll();

page_top('Ofertat turistike');
?>

<div class="section-intro">
    <div>
        <div class="eyebrow dark"> LUNÉA Voyages / OFERTAT </div>
        <h1> Eksploro ofertat turistike </h1>
        <p class="muted">Zgjidh destinacionin tënd të preferuar dhe rezervo udhëtimin tënd të ardhshëm.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="notice error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<div class="panel">

    <div class="panel-head">
        <h2>Ofertat e disponueshme</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Oferta</th>
                    <th>Destinacioni</th>
                    <th>Çmimi / person</th>
                    <th>Nisja</th>
                    <th>Kthimi</th>
                    <th>Vendet e lira</th>
                    <th>Rezervimi</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($ofertat as $oferta): ?>
                    <tr>
                        <td><?= e($oferta['titulli']) ?></td>

                        <td>
                            <?= e($oferta['destinacioni']) ?>,
                            <?= e($oferta['shteti']) ?>
                        </td>

                        <td><?= e($oferta['cmimi']) ?> €</td>
                        <td><?= e($oferta['data_nisjes']) ?></td>
                        <td><?= e($oferta['data_kthimit']) ?></td>
                        <td><?= e($oferta['vende_lira']) ?></td>

                        <td>
                            <?php if ($oferta['vende_lira'] > 0): ?>

                                <form method="POST" class="booking-form">

                                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                    <input type="hidden" name="oferta_id" value="<?= e($oferta['id']) ?>">
                                    <input type="number" name="numri_personave" min="1" max="<?= e($oferta['vende_lira']) ?>" value="1" aria-label="Numri i personave" required>

                                    <button type="submit" class="btn primary" data-loading="Duke rezervuar...">
                                        <i class="bx bx-calendar-check"></i>
                                        Rezervo
                                    </button>

                                </form>

                            <?php else: ?>

                                <span class="badge">Nuk ka vende</span>

                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$ofertat): ?>
                    <tr>
                        <td colspan="7" class="empty">Nuk ka oferta turistike të disponueshme.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php page_bottom(); ?>