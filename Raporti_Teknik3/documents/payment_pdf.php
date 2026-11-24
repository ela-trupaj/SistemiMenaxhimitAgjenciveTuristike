<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_login();

$user = current_user();
$roleId = (int)$user['roli_id'];
$paymentId = (int)($_GET['id'] ?? 0);

// Kontrollimi i ID-se se pageses
if ($paymentId <= 0) {
    http_response_code(400);
    exit('Pagesa nuk është e vlefshme.');
}

// Kontrollimi i autorizimit
if (!in_array($roleId, [ROLE_ADMIN, ROLE_STAFF, ROLE_CLIENT], true)) {
    http_response_code(403);
    exit('Nuk keni autorizim.');
}

// Marrja e te dhenave te pageses
$stmt = $db->prepare("
    SELECT
        pa.id,
        pa.rezervimi_id,
        pa.shuma,
        pa.metoda,
        pa.statusi,
        pa.data_pageses,
        r.perdoruesi_id,
        r.numri_personave,
        p.emri,
        p.mbiemri,
        p.email,
        o.titulli AS oferta,
        o.data_nisjes,
        o.data_kthimit,
        d.emri AS destinacioni,
        d.shteti
    FROM pagesat pa
    INNER JOIN rezervimet r ON r.id = pa.rezervimi_id
    INNER JOIN perdoruesit p ON p.id = r.perdoruesi_id
    INNER JOIN ofertat o ON o.id = r.oferta_id
    INNER JOIN destinacionet d ON d.id = o.destinacioni_id
    WHERE pa.id = ?
");

$stmt->execute([$paymentId]);
$pagesa = $stmt->fetch();

// Kontrollimi i pageses
if (!$pagesa) {
    http_response_code(404);
    exit('Pagesa nuk u gjet.');
}

// Klienti mund te shkarkoje vetem pagesat e veta
if (
    $roleId === ROLE_CLIENT &&
    (int)$pagesa['perdoruesi_id'] !== (int)$user['id']
) {
    http_response_code(403);
    exit('Nuk keni autorizim për këtë pagesë.');
}

// Vetem pagesat e perfunduara
if ($pagesa['statusi'] !== 'Paguar') {
    http_response_code(403);
    exit('Pagesa nuk është përfunduar.');
}

// Numri i pageses dhe rezervimit
$paymentNumber = str_pad((string)$paymentId, 5, '0', STR_PAD_LEFT);
$bookingNumber = str_pad((string)$pagesa['rezervimi_id'], 5, '0', STR_PAD_LEFT);
$fullName = trim($pagesa['emri'] . ' ' . ($pagesa['mbiemri'] ?? ''));

// Marrja e stilit te dokumentit
$css = file_get_contents(__DIR__ . '/../assets/pdf.css');

if ($css === false) {
    http_response_code(500);
    exit('Stili i dokumentit nuk u gjet.');
}

// Permbajtja e dokumentit PDF
ob_start();
?>

<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8">
    <title>Dëshmia e pagesës</title>

    <style>
        <?= $css ?>
    </style>
</head>

<body>

    <!-- HEADER -->
    <table class="header">
        <tr>
            <td>
                <h1>DËSHMIA E PAGESËS</h1>
                <div class="label">LV-P-<?= e($paymentNumber) ?></div>
            </td>

            <td class="brand">
                <div class="logo">LUNÉA</div>
                <div class="subtitle">VOYAGES</div>
            </td>
        </tr>
    </table>

    <!-- INFORMACIONET E PAGESËS DHE KLIENTIT -->
    <table class="info">
        <tr>
            <td>
                <div class="label">INFORMACIONET E DOKUMENTIT</div>

                <p><b>Agjencia:</b> LUNÉA Voyages</p>
                <p><b>Numri:</b> LV-P-<?= e($paymentNumber) ?></p>
                <p><b>Rezervimi:</b> LV-R-<?= e($bookingNumber) ?></p>
                <p><b>Data e lëshimit:</b> <?= date('d.m.Y') ?></p>
            </td>

            <td>
                <div class="label">INFORMACIONET E KLIENTIT</div>

                <p><b>Klienti:</b> <?= e($fullName) ?></p>
                <p><b>Email:</b> <?= e($pagesa['email']) ?></p>

                <p>
                    <b>Statusi:</b>
                    <span class="status">Paguar</span>
                </p>
            </td>
        </tr>
    </table>

    <!-- MESAZHI PER KLIENTIN -->
    <div class="message">
        <p><b>Përshëndetje, <?= e($fullName) ?>!</b></p>

        <p>
            Faleminderit që zgjodhët LUNÉA Voyages.
            Pagesa juaj është regjistruar me sukses.
            Më poshtë gjeni informacionet e rezervimit
            dhe detajet e transaksionit.
        </p>

        <p>Faleminderit për besimin tuaj!</p>
    </div>

    <!-- DETAJET E UDHETIMIT -->
    <h2>DETAJET E UDHËTIMIT</h2>

    <div class="trip">
        <table>
            <tr>
                <td>
                    <div class="label">DESTINACIONI</div>
                    <div class="destination">
                        <?= e($pagesa['destinacioni']) ?>
                    </div>
                    <?= e($pagesa['shteti']) ?>
                </td>

                <td>
                    <div class="label">OFERTA TURISTIKE</div>
                    <p><?= e($pagesa['oferta']) ?></p>
                    <b><?= e($pagesa['numri_personave']) ?> udhëtarë</b>
                </td>
            </tr>
        </table>

        <!-- DATAT E UDHETIMIT -->
        <table class="dates">
            <tr>
                <td>
                    <div class="label">DATA E NISJES</div>

                    <div class="date">
                        <?= $pagesa['data_nisjes'] ? e(date('d.m.Y', strtotime($pagesa['data_nisjes']))) : '—' ?>
                    </div>
                </td>

                <td>
                    <div class="label">DATA E KTHIMIT</div>

                    <div class="date">
                        <?= $pagesa['data_kthimit'] ? e(date('d.m.Y', strtotime($pagesa['data_kthimit']))) : '—' ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- DETAJET E PAGESËS -->
    <h2>DETAJET E PAGESËS</h2>

    <table class="details">
        <tr>
            <th>Numri i rezervimit</th>
            <th>Metoda e pagesës</th>
            <th>Data e pagesës</th>
        </tr>

        <tr>
            <td>#<?= e($bookingNumber) ?></td>
            <td><?= e($pagesa['metoda']) ?></td>

            <td>
                <?= $pagesa['data_pageses'] ? e(date('d.m.Y', strtotime($pagesa['data_pageses']))) : '—' ?>
            </td>
        </tr>
    </table>

    <!-- SHUMA E PAGUAR -->
    <table class="total">
        <tr>
            <td>SHUMA E PAGUAR</td>

            <td class="amount">
                <?= e(number_format((float)$pagesa['shuma'], 2)) ?> €
            </td>
        </tr>
    </table>

    <!-- INFORMACION I RENDESISHEM -->
    <div class="note">
        <b>Informacion i rëndësishëm</b>

        <br>

        Ky dokument dëshmon pagesën e regjistruar në sistemin
        e LUNÉA Voyages për rezervimin përkatës dhe nuk
        përbën faturë fiskale.
    </div>

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
    'LUNEA-Pagesa-' . $paymentNumber . '.pdf',
    ['Attachment' => true]
);

exit;