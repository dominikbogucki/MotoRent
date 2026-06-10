<?php
include 'includes/db.php';

header('Content-Type: application/json');

$samochod_id = (int)($_POST['samochod_id'] ?? 0);
$data_start  = $_POST['data_start']      ?? '';
$data_end    = $_POST['data_end']        ?? '';
$dodatki     = $_POST['dodatki']         ?? [];
$pakiet      = $_POST['pakiet_ochrony']  ?? 'podstawowy';

if ($samochod_id <= 0) {
    echo json_encode(['error' => 'Błędne ID pojazdu']);
    exit;
}

$stmt = $pdo->prepare("SELECT cena_za_dobe FROM samochody WHERE id = ?");
$stmt->execute([$samochod_id]);
$auto = $stmt->fetch();

if (!$auto) {
    echo json_encode(['error' => 'Nie znaleziono samochodu we flocie']);
    exit;
}

$cena_za_dobe = (float)$auto['cena_za_dobe'];
$laczna_cena  = 0;
$dni          = 0;
$dostepny     = true;
$komunikat    = '';

if ($data_start !== '' && $data_end !== '' && $data_start <= $data_end) {
    $dni = (int)ceil((strtotime($data_end) - strtotime($data_start)) / 86400) + 1;

    $stmt_kolizja = $pdo->prepare("
        SELECT COUNT(*) FROM wypozyczenia w
        JOIN historia_pojazdow h ON w.id_historii_samochodu = h.id
        WHERE h.oryginalne_id_auta = ?
        AND w.status IN ('oczekuje', 'zatwierdzone')
        AND NOT (w.data_end < ? OR w.data_start > ?)
    ");
    $stmt_kolizja->execute([$samochod_id, $data_start, $data_end]);

    if ($stmt_kolizja->fetchColumn() > 0) {
        $dostepny  = false;
        $komunikat = '⚠️ Ten samochód jest już zarezerwowany w tym terminie!';
    }

    if ($dostepny) {
        $cena_dzienna = $cena_za_dobe;

        if (in_array('fotelik', $dodatki, true)) $cena_dzienna += 20;
        if (in_array('gps',     $dodatki, true)) $cena_dzienna += 15;

        if ($pakiet === 'premium') $cena_dzienna += 40;
        elseif ($pakiet === 'vip') $cena_dzienna += 80;

        $laczna_cena = $dni * $cena_dzienna;
    }
}

echo json_encode([
    'dostepny'    => $dostepny,
    'komunikat'   => $komunikat,
    'dni'         => $dni,
    'laczna_cena' => number_format($laczna_cena, 2, ',', ' ') . ' zł',
]);
