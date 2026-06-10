<?php
include 'includes/db.php';
session_start();

$komunikat = '';
$wpisany_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $haslo = $_POST['haslo'] ?? '';
    $wpisany_email = $email;

    if ($email !== '' && $haslo !== '') {
        $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($haslo, $user['haslo'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['imie'];
            $_SESSION['rola']      = $user['rola'];

            $cel = $_SESSION['redirect_url'] ?? 'index.php';
            unset($_SESSION['redirect_url']);
            header("Location: $cel");
            exit;
        }

        $komunikat = "<p class='error-msg'>Błędny e-mail lub hasło!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoRent - Zaloguj się</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body class="auth-body">
    <a href="index.php" class="back-to-home">
        <span class="material-symbols-outlined">arrow_back</span>Powrót do strony głównej
    </a>
    <div class="auth-card">
        <h2>Zaloguj <span>Się</span></h2>
        <p class="auth-subtitle">Witaj ponownie! Wprowadź swoje dane, aby kontynuować.</p>

        <?= $komunikat ?>

        <form action="logowanie.php" method="POST" class="auth-form" id="loginForm">
            <div class="input-group">
                <input type="email" name="email" class="auth-input" placeholder="Adres e-mail" required autocomplete="email" value="<?= htmlspecialchars($wpisany_email) ?>">
                <div class="input-spacing"></div>
            </div>

            <div class="input-group">
                <input type="password" name="haslo" class="auth-input" placeholder="Hasło" required>
                <div class="input-spacing"></div>
            </div>

            <button type="submit" class="auth-submit-btn">Zaloguj się</button>
        </form>

        <p class="auth-footer-text">Nie masz jeszcze konta? <a href="rejestracja.php">Zarejestruj się</a></p>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
