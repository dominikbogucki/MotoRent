<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: flota.php");
    exit;
}

$samochod_id = (int)($_POST['samochod_id'] ?? 0);
$data_start  = trim($_POST['data_start'] ?? '');
$data_end    = trim($_POST['data_end'] ?? '');
$dodatki     = $_POST['dodatki'] ?? [];
$pakiet      = trim($_POST['pakiet_ochrony'] ?? 'podstawowy');

if (!in_array($pakiet, ['podstawowy', 'premium', 'vip'], true)) {
    $pakiet = 'podstawowy';
}

if ($samochod_id <= 0 || $data_start === '' || $data_end === '') {
    header("Location: flota.php");
    exit;
}

$dzis = date('Y-m-d');
if ($data_start < $dzis) {
    header("Location: samochod.php?id=$samochod_id&err=" . urlencode("Data rozpoczęcia nie może być z przeszłości!"));
    exit;
}
if ($data_start > $data_end) {
    header("Location: samochod.php?id=$samochod_id&err=" . urlencode("Data zakończenia nie może być wcześniejsza niż data rozpoczęcia!"));
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM samochody WHERE id = ? FOR UPDATE");
    $stmt->execute([$samochod_id]);
    $auto = $stmt->fetch();

    if (!$auto) {
        throw new Exception("Nie odnaleziono wybranego pojazdu we flocie!");
    }
    if ((int)$auto['dostepny'] !== 1) {
        throw new Exception("Ten pojazd jest obecnie wyłączony z floty lub niedostępny!");
    }

    $stmt_kolizja = $pdo->prepare("
        SELECT COUNT(*) FROM wypozyczenia w
        JOIN historia_pojazdow h ON w.id_historii_samochodu = h.id
        WHERE h.oryginalne_id_auta = ?
        AND w.status IN ('oczekuje', 'zatwierdzone')
        AND NOT (w.data_end < ? OR w.data_start > ?)
    ");
    $stmt_kolizja->execute([$samochod_id, $data_start, $data_end]);

    if ($stmt_kolizja->fetchColumn() > 0) {
        throw new Exception("Przepraszamy, ten pojazd został już zarezerwowany w wybranym terminie przez innego klienta!");
    }

    $dni = (int)ceil((strtotime($data_end) - strtotime($data_start)) / 86400) + 1;

    $cena_dzienna = (float)$auto['cena_za_dobe'];
    if (in_array('fotelik', $dodatki, true)) $cena_dzienna += 20;
    if (in_array('gps',     $dodatki, true)) $cena_dzienna += 15;
    if ($pakiet === 'premium') $cena_dzienna += 40;
    elseif ($pakiet === 'vip') $cena_dzienna += 80;

    $koszt_calkowity = $dni * $cena_dzienna;

    $stmt_historia = $pdo->prepare("
        INSERT INTO historia_pojazdow (oryginalne_id_auta, marka, model, cena_za_dobe_wtedy)
        VALUES (?, ?, ?, ?)
    ");
    $stmt_historia->execute([$auto['id'], $auto['marka'], $auto['model'], $auto['cena_za_dobe']]);
    $id_historii = $pdo->lastInsertId();

    $stmt_hist_check = $pdo->prepare("SELECT id FROM historia_uzytkownikow WHERE oryginalne_id_uzytkownika = ?");
    $stmt_hist_check->execute([$_SESSION['user_id']]);
    $id_historii_uzytkownika = $stmt_hist_check->fetchColumn();

    if (!$id_historii_uzytkownika) {
        $stmt_user_data = $pdo->prepare("SELECT imie, nazwisko, email FROM uzytkownicy WHERE id = ?");
        $stmt_user_data->execute([$_SESSION['user_id']]);
        $u_data = $stmt_user_data->fetch();

        if ($u_data) {
            $stmt_user_hist = $pdo->prepare("
                INSERT INTO historia_uzytkownikow (oryginalne_id_uzytkownika, imie, nazwisko, email)
                VALUES (?, ?, ?, ?)
            ");
            $stmt_user_hist->execute([$_SESSION['user_id'], $u_data['imie'], $u_data['nazwisko'], $u_data['email']]);
            $id_historii_uzytkownika = $pdo->lastInsertId();
        }
    }

    $stmt_rez = $pdo->prepare("
        INSERT INTO wypozyczenia (id_uzytkownika, id_historii_uzytkownika, id_historii_samochodu,
                                  pakiet_ochrony, data_start, data_end, status, koszt_calkowity)
        VALUES (?, ?, ?, ?, ?, ?, 'oczekuje', ?)
    ");
    $stmt_rez->execute([
        $_SESSION['user_id'],
        $id_historii_uzytkownika,
        $id_historii,
        $pakiet,
        $data_start,
        $data_end,
        $koszt_calkowity,
    ]);

    $pdo->commit();

    header("Location: moje_konto.php?msg=" . urlencode("Rezerwacja pojazdu została pomyślnie złożona! Oczekuje na zatwierdzenie."));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header("Location: samochod.php?id=$samochod_id&err=" . urlencode($e->getMessage()));
    exit;
}
