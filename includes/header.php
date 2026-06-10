<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$aktualna_strona = basename($_SERVER['PHP_SELF']);
$style_per_page = [
    'index.php'      => 'css/index.css',
    'flota.php'      => 'css/fleet.css',
    'samochod.php'   => 'css/car-detail.css',
    'moje_konto.php' => 'css/account.css',
    'admin.php'      => 'css/admin.css',
    'logowanie.php'  => 'css/auth.css',
    'rejestracja.php'=> 'css/auth.css',
    'o-nas.php'      => 'css/about.css',
];
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoRent - Wypożyczalnia Samochodów</title>
    <link rel="stylesheet" href="css/global.css">
    <?php if (isset($style_per_page[$aktualna_strona])): ?>
        <link rel="stylesheet" href="<?= $style_per_page[$aktualna_strona] ?>">
    <?php endif; ?>
    <?php
    if ($aktualna_strona === 'samochod.php'):
    ?>
        <link rel="stylesheet" href="css/auth.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>

<body>
    <nav class="nav-bar">
        <div class="nav-container">
            <a href="index.php" class="logo"><span>Moto</span>Rent</a>
            <div class="links">
                <a href="index.php">Strona Główna</a>
                <a href="flota.php">Nasza Flota</a>
                <a href="o-nas.php">O nas</a>
            </div>
            <div class="login">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="profile-dropdown" id="profileDropdown">

                        <div class="profile-trigger">
                            <div class="profile-avatar">
                                <span class="material-symbols-outlined">account_circle</span>
                            </div>
                        </div>

                        <div class="dropdown-menu">
                            <span class="user-welcome">
                                Witaj, <span><?= $_SESSION['user_name']; ?></span>!
                            </span>

                            <div class="dropdown-divider"></div>

                            <a href="moje_konto.php" class="dropdown-item">
                                <span class="material-symbols-outlined">manage_accounts</span>
                                Moje konto
                            </a>

                            <?php if (isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin'): ?>
                                <a href="admin.php" class="dropdown-item dropdown-item-admin">
                                    <span class="material-symbols-outlined">admin_panel_settings</span>
                                    Panel admina
                                </a>
                            <?php endif; ?>

                            <a href="wyloguj.php" class="dropdown-item logout-item">
                                <span class="material-symbols-outlined">logout</span>
                                Wyloguj się
                            </a>
                        </div>

                    </div>
                <?php else: ?>
                    <a href="logowanie.php" class="login-btn">Logowanie</a>
                    <a href="rejestracja.php" class="register-btn">Rejestracja</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>