<?php
include 'includes/db.php';
session_start();

$wpisane_imie = '';
$wpisane_nazwisko = '';
$wpisany_email = '';
$wpisana_data_ur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imie     = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $data_ur  = $_POST['data_urodzenia'] ?? '';
    $haslo    = $_POST['haslo'] ?? '';

    $wpisane_imie     = $imie;
    $wpisane_nazwisko = $nazwisko;
    $wpisany_email    = $email;
    $wpisana_data_ur  = $data_ur;

    if ($imie !== '' && $nazwisko !== '' && $email !== '' && $data_ur !== '' && $haslo !== '') {
        $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO uzytkownicy (imie, nazwisko, email, data_urodzenia, haslo, rola)
            VALUES (?, ?, ?, ?, ?, 'klient')
        ");
        if ($stmt->execute([$imie, $nazwisko, $email, $data_ur, $haslo_hash])) {
            $_SESSION['user_id']   = $pdo->lastInsertId();
            $_SESSION['user_name'] = $imie;
            $_SESSION['rola']      = 'klient';

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoRent - Stwórz konto</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body class="auth-body">
    <a href="index.php" class="back-to-home">
        <span class="material-symbols-outlined">arrow_back</span>Powrót do strony głównej
    </a>
    <div class="auth-card">
        <h2>Stwórz <span>Konto</span></h2>
        <p class="auth-subtitle">Dołącz do nas i zyskaj dostęp do najlepszej floty w mieście.</p>

        <div id="js-error-container"></div>

        <form action="rejestracja.php" method="POST" class="auth-form" id="registerForm">

            <div class="input-row">
                <div class="input-group">
                    <input type="text" name="imie" id="regImie" class="auth-input" placeholder="Imię" required autocomplete="given-name" value="<?= htmlspecialchars($wpisane_imie) ?>">
                    <span class="field-error" id="error-imie"></span>
                </div>

                <div class="input-group">
                    <input type="text" name="nazwisko" id="regNazwisko" class="auth-input" placeholder="Nazwisko" required autocomplete="family-name" value="<?= htmlspecialchars($wpisane_nazwisko) ?>">
                    <span class="field-error" id="error-nazwisko"></span>
                </div>
            </div>

            <div class="input-group">
                <input type="date" name="data_urodzenia" id="regDataUr" class="auth-input" max="<?= date('Y-m-d') ?>" required value="<?= htmlspecialchars($wpisana_data_ur) ?>">
                <span class="field-error" id="error-data-ur"></span>
            </div>

            <div class="input-group">
                <input type="email" name="email" id="regEmail" class="auth-input" placeholder="Adres e-mail" required autocomplete="email" value="<?= htmlspecialchars($wpisany_email) ?>">
                <span class="field-error" id="error-email"></span>
            </div>

            <div class="input-group">
                <input type="password" name="haslo" id="regPassword" class="auth-input" placeholder="Hasło" required autocomplete="new-password">
                <span class="field-error" id="error-haslo"></span>
            </div>

            <button type="submit" class="auth-submit-btn">Zarejestruj się</button>
        </form>

        <p class="auth-footer-text">Masz już konto? <a href="logowanie.php">Zaloguj się</a></p>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
