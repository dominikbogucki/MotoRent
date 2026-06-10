<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';

if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$akcja = $_GET['akcja'] ?? '';

if ($akcja === 'dodaj_auto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $marka         = trim($_POST['marka'] ?? '');
    $model         = trim($_POST['model'] ?? '');
    $kategoria     = $_POST['kategoria'] ?? '';
    $cena_za_dobe  = (float)($_POST['cena_za_dobe'] ?? 0);
    $lokalizacja   = $_POST['lokalizacja'] ?? '';
    $paliwo        = trim($_POST['paliwo'] ?? '');
    $moc           = (int)($_POST['moc'] ?? 0);
    $skrzynia      = trim($_POST['skrzynia'] ?? '');
    $liczba_drzwi  = (int)($_POST['liczba_drzwi'] ?? 0);
    $rok_produkcji = (int)($_POST['rok_produkcji'] ?? 0);
    $pojemnosc_bag = (int)($_POST['pojemnosc_bagaznika'] ?? 0);

    $nazwa_zdjecia = '';
    if (!empty($_FILES['zdjecie']['name']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
        $rozszerzenie = strtolower(pathinfo($_FILES['zdjecie']['name'], PATHINFO_EXTENSION));
        $dozwolone    = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($rozszerzenie, $dozwolone)) {
            $nazwa_zdjecia = uniqid('car_') . '.' . $rozszerzenie;
            move_uploaded_file($_FILES['zdjecie']['tmp_name'], __DIR__ . '/images/' . $nazwa_zdjecia);
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO samochody
        (marka, model, kategoria, cena_za_dobe, lokalizacja, paliwo, moc, skrzynia,
         liczba_drzwi, rok_produkcji, pojemnosc_bagaznika, zdjecie, dostepny)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
        $marka, $model, $kategoria, $cena_za_dobe, $lokalizacja, $paliwo, $moc, $skrzynia,
        $liczba_drzwi, $rok_produkcji, $pojemnosc_bag, $nazwa_zdjecia
    ]);

    header("Location: admin.php?view=auta&msg=" . urlencode("Pojazd '$marka $model' został dodany do floty."));
    exit;
}

if ($akcja === 'edytuj_auto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = (int)$_POST['id'];
    $marka         = trim($_POST['marka']);
    $model         = trim($_POST['model']);
    $cena_za_dobe  = (float)$_POST['cena_za_dobe'];
    $dostepny      = (int)$_POST['dostepny'];
    $moc           = (int)$_POST['moc'];
    $skrzynia      = trim($_POST['skrzynia']);
    $rok_produkcji = (int)$_POST['rok_produkcji'];

    $stmt = $pdo->prepare("
        UPDATE samochody
        SET marka = ?, model = ?, cena_za_dobe = ?, dostepny = ?, moc = ?, skrzynia = ?, rok_produkcji = ?
        WHERE id = ?
    ");
    $stmt->execute([$marka, $model, $cena_za_dobe, $dostepny, $moc, $skrzynia, $rok_produkcji, $id]);

    header("Location: admin.php?view=auta&msg=" . urlencode("Pojazd zaktualizowany pomyślnie."));
    exit;
}

if ($akcja === 'usun_auto') {
    $id = (int)$_GET['id'];

    $stmt_check = $pdo->prepare("
        SELECT COUNT(*) FROM wypozyczenia w
        JOIN historia_pojazdow h ON w.id_historii_samochodu = h.id
        WHERE h.oryginalne_id_auta = ?
        AND w.status IN ('oczekuje', 'zatwierdzone')
    ");
    $stmt_check->execute([$id]);

    if ($stmt_check->fetchColumn() > 0) {
        header("Location: admin.php?view=auta&err=" . urlencode("Nie można usunąć tego pojazdu - ma aktywne rezerwacje."));
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM samochody WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin.php?view=auta&msg=" . urlencode("Pojazd usunięty z bazy."));
    exit;
}

if ($akcja === 'zmien_status') {
    $id     = (int)($_GET['id'] ?? 0);
    $status = $_GET['status'] ?? '';

    $dozwolone_statusy = ['oczekuje', 'zatwierdzone', 'zakonczone', 'anulowane'];

    if ($id > 0 && in_array($status, $dozwolone_statusy)) {
        $stmt = $pdo->prepare("UPDATE wypozyczenia SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        header("Location: admin.php?view=rezerwacje&msg=" . urlencode("Status rezerwacji #$id zaktualizowany."));
    } else {
        header("Location: admin.php?view=rezerwacje&err=" . urlencode("Błędne parametry zmiany statusu."));
    }
    exit;
}

if ($akcja === 'usun_uzytkownika') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id === (int)$_SESSION['user_id']) {
        header("Location: admin.php?view=uzytkownicy&err=" . urlencode("Nie możesz usunąć swojego konta."));
        exit;
    }

    if ($id > 0) {
        $stmt_anuluj = $pdo->prepare("
            UPDATE wypozyczenia
            SET status = 'anulowane'
            WHERE id_uzytkownika = ?
            AND status IN ('oczekuje', 'zatwierdzone')
        ");
        $stmt_anuluj->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM uzytkownicy WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin.php?view=uzytkownicy&msg=" . urlencode("Użytkownik #$id usunięty."));
    } else {
        header("Location: admin.php?view=uzytkownicy&err=" . urlencode("Niepoprawne ID użytkownika."));
    }
    exit;
}