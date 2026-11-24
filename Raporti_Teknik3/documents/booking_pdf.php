<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_login();

$user = current_user();
$roleId = (int)$user['roli_id'];
$bookingId = (int)($_GET['id'] ?? 0);

// Kontrollimi i ID-se se rezervimit
if ($bookingId <= 0) {
    http_response_code(400);
    exit('Rezervimi nuk është i vlefshëm.');
}

// Kontrollimi i autorizimit
if (!in_array($roleId, [ROLE_ADMIN, ROLE_STAFF, ROLE_CLIENT], true)) {
    http_response_code(403);
    exit('Nuk keni autorizim.');
}

// Marrja e te dhenave te rezervimit
$stmt = $db->prepare("
    SELECT
        r.id,
        r.perdoruesi_id,
        r.numri_personave,
        r.data_rezervimit,
        r.statusi,
        p.emri,
        p.mbiemri,
        p.email,
        o.titulli AS oferta,
        o.cmimi,
        o.data_nisjes,
        o.data_kthimit,
        d.emri AS destinacioni,
        d.shteti
    FROM rezervimet r
    INNER JOIN perdoruesit p ON p.id = r.perdoruesi_id
    INNER JOIN ofertat o ON o.id = r.oferta_id
    INNER JOIN destinacionet d ON d.id = o.destinacioni_id
    WHERE r.id = ?
");

$stmt->execute([$bookingId]);
$rezervimi = $stmt->fetch();

// Kontrollimi i rezervimit
if (!$rezervimi) {
    http_response_code(404);
    exit('Rezervimi nuk u gjet.');
}

// Klienti mund te shkarkoje vetem rezervimet e veta
if (
    $roleId === ROLE_CLIENT &&
    (int)$rezervimi['perdoruesi_id'] !== (int)$user['id']
) {
    http_response_code(403);
    exit('Nuk keni autorizim për këtë rezervim.');
}

// Vetem rezervimet e konfirmuara
if ($rezervimi['statusi'] !== 'Konfirmuar') {
    http_response_code(403);
    exit('Rezervimi nuk është konfirmuar.');
}

// Llogaritja e cmimit total
$totali = (float)$rezervimi['cmimi'] * (int)$rezervimi['numri_personave'];

$bookingNumber = str_pad((string)$bookingId, 5, '0', STR_PAD_LEFT);

$fullName = trim($rezervimi['emri'] . ' ' . ($rezervimi['mbiemri'] ?? ''));

// Marrja e stilit te dokumentit
$css = file_get_contents(__DIR__ . '/../assets/pdf.css');

if ($css === false) {
    http_response_code(500);
    exit('Stili i dokumentit nuk u gjet.');
}

// Fotografite e destinacioneve
// $images = [
//     'paris' => 'Paris.png',
//     'romë' => 'Rome.png',
//     'rome' => 'Rome.png',
//     'roma' => 'Rome.png',
//     'dubai' => 'Dubai .png',
//     'stamboll' => 'Instambul.png',
//     'istanbul' => 'Instambul.png',
//     'instambul' => 'Instambul.png',
//     'santorini' => 'Santorin.png',
//     'santorin' => 'Santorin.png'
// ];

// Marrja e fotografise se destinacionit
// $destinationKey = mb_strtolower(trim($rezervimi['destinacioni']), 'UTF-8');
// $imageData = null;

// if (isset($images[$destinationKey])) {
//     $imagePath = __DIR__ . '/../assets/images/' . $images[$destinationKey];

//     if (is_readable($imagePath)) {
//         $imageContent = file_get_contents($imagePath);

//         if ($imageContent !== false) {
//             $imageData = 'data:image/png;base64,' . base64_encode($imageContent);
//         }
//     }
// }

// Permbajtja e dokumentit PDF
ob_start();
?>

<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8">
    <title>Konfirmim rezervimi</title>

    <style>
        <?= $css ?>
    </style>
</head>

<body>

    <!-- HEADER -->
    <table class="header">
        <tr>
            <td>
                <h1>KONFIRMIM REZERVIMI</h1>
                <div class="label">LV-R-<?= e($bookingNumber) ?></div>
            </td>

            <td class="brand">
                <div class="logo">LUNÉA</div>
                <div class="subtitle">VOYAGES</div>
            </td>
        </tr>
    </table>

    <!-- INFORMACIONET E REZERVIMIT DHE KLIENTIT -->
    <table class="info">
        <tr>
            <td>
                <div class="label">INFORMACIONET E REZERVIMIT</div>

                <p><b>Agjencia:</b> LUNÉA Voyages</p>
                <p><b>Numri:</b> LV-R-<?= e($bookingNumber) ?></p>
                <p><b>Data e lëshimit:</b> <?= date('d.m.Y') ?></p>

                <p>
                    <b>Data e rezervimit:</b>
                    <?= $rezervimi['data_rezervimit'] ? e(date('d.m.Y', strtotime($rezervimi['data_rezervimit']))) : '—' ?>
                </p>
            </td>

            <td>
                <div class="label">INFORMACIONET E KLIENTIT</div>

                <p><b>Klienti:</b> <?= e($fullName) ?></p>
                <p><b>Email:</b> <?= e($rezervimi['email']) ?></p>

                <p>
                    <b>Statusi:</b>
                    <span class="status">Konfirmuar</span>
                </p>
            </td>
        </tr>
    </table>

    <!-- MESAZHI PER KLIENTIN -->
    <div class="message">
        <p><b>Përshëndetje, <?= e($fullName) ?>!</b></p>

        <p>
            Faleminderit që zgjodhët LUNÉA Voyages.
            Rezervimi juaj është konfirmuar me sukses.
            Ju lutemi, kontrolloni informacionet e udhëtimit
            të paraqitura më poshtë.
        </p>

        <p>Ju dëshirojmë një udhëtim të mrekullueshëm!</p>
    </div>

    <!-- DETAJET E UDHETIMIT -->
    <h2>DETAJET E UDHËTIMIT</h2>

    <div class="trip">
        <table>
            <tr>
                <td>
                    <div class="label">DESTINACIONI</div>

                    <div class="destination">
                        <?= e($rezervimi['destinacioni']) ?>
                    </div>

                    <?= e($rezervimi['shteti']) ?>
                </td>

                <td>
                    <div class="label">OFERTA TURISTIKE</div>
                    <p><?= e($rezervimi['oferta']) ?></p>
                    <b><?= e($rezervimi['numri_personave']) ?> udhëtarë</b>
                </td>
            </tr>
        </table>

        <!-- DATAT E UDHETIMIT -->
        <table class="dates">
            <tr>
                <td>
                    <div class="label">DATA E NISJES</div>

                    <div class="date">
                        <?= $rezervimi['data_nisjes'] ? e(date('d.m.Y', strtotime($rezervimi['data_nisjes']))) : '—' ?>
                    </div>
                </td>

                <td>
                    <div class="label">DATA E KTHIMIT</div>

                    <div class="date">
                        <?= $rezervimi['data_kthimit'] ? e(date('d.m.Y', strtotime($rezervimi['data_kthimit']))) : '—' ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- INFORMACIONET E OFERTES -->
    <table class="details">
        <tr>
            <th>Oferta turistike</th>
            <th>Persona</th>
            <th>Çmimi aktual / person</th>
        </tr>

        <tr>
            <td><?= e($rezervimi['oferta']) ?></td>
            <td><?= e($rezervimi['numri_personave']) ?></td>
            <td><?= e(number_format((float)$rezervimi['cmimi'], 2)) ?> €</td>
        </tr>
    </table>

    <!-- CMIMI TOTAL -->
    <table class="total">
        <tr>
            <td>VLERA TOTALE SIPAS ÇMIMIT AKTUAL</td>

            <td class="amount">
                <?= e(number_format($totali, 2)) ?> €
            </td>
        </tr>
    </table>

    <!-- INFORMACION I RENDESISHEM -->
    <div class="note">
        <b>Informacion i rëndësishëm</b>

        <br>

        Ky dokument konfirmon rezervimin tuaj në sistemin
        e LUNÉA Voyages dhe nuk përbën dëshmi të pagesës.
    </div>

    <!-- FOTOGRAFIA E DESTINACIONIT -->
    <!-- <?php if ($imageData !== null): ?>

        <div class="photo">
            <img src="<?= e($imageData) ?>" alt="Destinacioni">

            <div class="photo-caption">
                <?= e($rezervimi['destinacioni'] . ', ' . $rezervimi['shteti']) ?>
            </div>
        </div>

    <?php endif; ?> -->

    <!-- FOOTER -->
    <div class="footer">
        © <?= date('Y') ?> LUNÉA Voyages.
        Të gjitha të drejtat e rezervuara.

        <br>

        Dokumenti është gjeneruar automatikisht nga sistemi.
    </div>

</body>
</html>

<?php

$html = ob_get_clean();

// Konfigurimi i Dompdf
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Shkarkimi i dokumentit PDF
$dompdf->stream(
    'LUNEA-Rezervimi-' . $bookingNumber . '.pdf',
    ['Attachment' => true]
);

exit;