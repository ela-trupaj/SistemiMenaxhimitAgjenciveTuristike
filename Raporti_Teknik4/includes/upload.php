<?php
declare(strict_types=1);

function upload_image(array $file, string $folder): string
{
    // Kontrollimi i folderit
    if (!in_array($folder, ['destinations', 'offers'], true)) {
        throw new RuntimeException(
            'Folderi nuk është i vlefshëm.'
        );
    }

    // Kontrollimi i ngarkimit
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(
            'Ngarkimi i fotografisë dështoi.'
        );
    }

    // Madhesia maksimale: 5 MB
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException(
            'Fotografia nuk duhet të kalojë 5 MB.'
        );
    }

    // Kontrollimi i skedarit
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException(
            'Skedari nuk është i vlefshëm.'
        );
    }

    // Formatet e lejuara
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    // Kontrollimi i formatit te fotografise
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (
        !isset($allowed[$mime]) ||
        !getimagesize($file['tmp_name'])
    ) {
        throw new RuntimeException(
            'Lejohen vetëm fotografitë JPG, PNG dhe WEBP.'
        );
    }

    // Gjenerimi i emrit unik
    $filename = bin2hex(random_bytes(16))
        . '.' . $allowed[$mime];

    // Rruga e folderit
    $directory = dirname(__DIR__)
        . '/assets/uploads/' . $folder . '/';

    // Krijimi i folderit nese nuk ekziston
    if (!is_dir($directory)) {
        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException(
                'Folderi nuk mund të krijohet.'
            );
        }
    }

    // Ruajtja e fotografise
    if (!move_uploaded_file(
        $file['tmp_name'],
        $directory . $filename
    )) {
        throw new RuntimeException(
            'Fotografia nuk mund të ruhet.'
        );
    }

    return 'assets/uploads/' . $folder . '/' . $filename;
}

// Ngarkimi i fotografise se destinacionit
function upload_destination_image(array $file): string
{
    return upload_image($file, 'destinations');
}

// Ngarkimi i fotografise se ofertes
function upload_offer_image(array $file): string
{
    return upload_image($file, 'offers');
}