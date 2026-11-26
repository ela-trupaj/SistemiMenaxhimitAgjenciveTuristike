<?php
$entities = [
    'destinacionet' => [
        'title' => 'Destinacionet',
        'fields' => [
            'emri' => ['Emri', 'text', true],
            'shteti' => ['Shteti', 'text', true],
            'pershkrimi' => ['Përshkrimi', 'textarea', false],
            'foto' => ['Fotografia', 'file', false],
            'statusi' => ['Statusi', 'enum:Aktiv|Joaktiv', true]
        ],
        'list' => 'SELECT * FROM destinacionet ORDER BY id DESC'
    ],
    'ofertat' => [
    'title' => 'Ofertat turistike',
    'fields' => [
        'destinacioni_id' => ['Destinacioni', 'select:destinacionet:emri', true],
        'titulli' => ['Titulli', 'text', true],
        'pershkrimi' => ['Përshkrimi', 'textarea', false],
        'cmimi' => ['Çmimi për person (€)', 'decimal', true],
        'kapaciteti' => ['Kapaciteti', 'integer', true],
        'data_nisjes' => ['Data e nisjes', 'date', true],
        'data_kthimit' => ['Data e kthimit', 'date', true],
        'vendi_nisjes' => ['Vendi i nisjes', 'text', true],
        'transporti' => ['Transporti', 'enum:Aeroplan|Autobus|Anije|Transport individual', true],
        'akomodimi' => ['Akomodimi', 'text', false],
        'paketa_perfshin' => ['Paketa përfshin', 'textarea', true],
        'paketa_nuk_perfshin' => ['Paketa nuk përfshin', 'textarea', false],
        'foto' => ['Fotografia e ofertës', 'file', false],
        'statusi' => ['Statusi', 'enum:Aktive|Joaktive', true]
    ],
    'list' => 'SELECT o.*, d.emri AS destinacioni FROM ofertat o JOIN destinacionet d ON d.id = o.destinacioni_id ORDER BY o.id DESC'
],
    'rezervimet' => [
        'title' => 'Rezervimet',
        'fields' => [
            'perdoruesi_id' => ['Përdoruesi', 'select:perdoruesit:emri', true],
            'oferta_id' => ['Oferta', 'select:ofertat:titulli', true],
            'numri_personave' => ['Numri i personave', 'integer', true],
            'statusi' => ['Statusi', 'enum:Ne pritje|Konfirmuar|Anuluar', true]
        ],
        'list' => 'SELECT r.*, u.emri AS perdoruesi, o.titulli AS oferta FROM rezervimet r JOIN perdoruesit u ON u.id = r.perdoruesi_id JOIN ofertat o ON o.id = r.oferta_id ORDER BY r.id DESC'
    ],
    'pagesat' => [
        'title' => 'Pagesat',
        'fields' => [
            'rezervimi_id' => ['Rezervimi', 'select:rezervimet:id', true],
            'shuma' => ['Shuma (€)', 'decimal', true],
            'metoda' => ['Metoda', 'enum:Kartelë|Cash|Transfer', true],
            'statusi' => ['Statusi', 'enum:Ne pritje|Paguar|Deshtuar', true]
        ],
        'list' => 'SELECT p.*, r.statusi AS rezervimi_status FROM pagesat p JOIN rezervimet r ON r.id = p.rezervimi_id ORDER BY p.id DESC'
    ]
];

function validate_entity(PDO $db, string $entity, array $fields, array $input, bool $fieldErrors = false): array {
    $clean = [];
    $errors = [];

    foreach ($fields as $key => $f) {

        if ($f[1] === 'file') {
            continue;
        }

        $v = trim((string)($input[$key] ?? ''));
        $type = $f[1];

        if ($f[2] && $v === '') {
            $errors[$key] = $f[0] . ' është e detyrueshme.';
            continue;
        }

        if ($v === '') {
            $clean[$key] = null;
            continue;
        }

        if (str_starts_with($type, 'select:')) {
            $parts = explode(':', $type);

            if (!ctype_digit($v)) {
                $errors[$key] = 'ID e pavlefshme.';
            } else {
                $s = $db->prepare('SELECT COUNT(*) FROM ' . $parts[1] . ' WHERE id = ?');
                $s->execute([$v]);

                if (!$s->fetchColumn()) {
                    $errors[$key] = $f[0] . ' nuk ekziston.';
                }
            }

            $clean[$key] = (int)$v;
        } elseif ($type === 'integer') {
            if (
                filter_var($v, FILTER_VALIDATE_INT) === false ||
                (int)$v < 0 ||
                ($key === 'numri_personave' && (int)$v < 1)
            ) {
                $errors[$key] = $f[0] . ' duhet të jetë numër i vlefshëm.';
            }

            $clean[$key] = (int)$v;
        } elseif ($type === 'decimal') {
            if (!is_numeric($v) || (float)$v < 0) {
                $errors[$key] = $f[0] . ' duhet të jetë shumë pozitive.';
            }

            $clean[$key] = $v;
        } elseif ($type === 'date') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $v);

            if (!$date || $date->format('Y-m-d') !== $v) {
                $errors[$key] = 'Data nuk është e vlefshme.';
            }

            $clean[$key] = $v;
        } elseif (str_starts_with($type, 'enum:')) {
            if (!in_array($v, explode('|', substr($type, 5)), true)) {
                $errors[$key] = 'Vlerë e pavlefshme për ' . $f[0];
            }

            $clean[$key] = $v;
        } else {
            $clean[$key] = $v;
        }
    }

    if (
        $entity === 'ofertat' &&
        isset($clean['data_nisjes'], $clean['data_kthimit']) &&
        $clean['data_kthimit'] < $clean['data_nisjes']
    ) {
        $errors['data_kthimit'] = 'Data e kthimit nuk mund të jetë para nisjes.';
    }

    return [$clean, $fieldErrors ? $errors : array_values($errors)];
}

function persist_entity(PDO $db, string $entity, array $fields, array $input, ?int $id, bool $fieldErrors = false): array {
    [$clean, $errors] = validate_entity($db, $entity, $fields, $input, $fieldErrors);

    if ($errors) {
        return $errors;
    }

    // Ruajtja e fotografive te destinacioneve dhe ofertave
    if (
        in_array($entity, ['destinacionet', 'ofertat'], true) &&
        isset($input['foto'])
    ) {
        $clean['foto'] = $input['foto'];
    }

    $keys = array_keys($clean);

    try {
        if ($id) {
            $sql = 'UPDATE ' . $entity . ' SET ' .
                implode(',', array_map(fn($k) => $k . ' = ?', $keys)) .
                ' WHERE id = ?';

            $params = array_values($clean);
            $params[] = $id;
        } else {
            $sql = 'INSERT INTO ' . $entity .
                ' (' . implode(',', $keys) . ') VALUES (' .
                implode(',', array_fill(0, count($keys), '?')) . ')';

            $params = array_values($clean);
        }

        $db->prepare($sql)->execute($params);
    } catch (PDOException $e) {
        return $fieldErrors
            ? ['general' => 'Ruajtja dështoi. Ju lutemi, provoni përsëri.']
            : ['Ruajtja dështoi. Kontrolloni lidhjet, vlerat unike dhe kufizimet e databazës.'];
    }

    return [];
}
