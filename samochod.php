<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';

if (empty($_GET['id'])) {
    header("Location: flota.php");
    exit;
}
$car_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM samochody WHERE id = ?");
$stmt->execute([$car_id]);
$auto = $stmt->fetch();

if (!$auto) {
    header("Location: flota.php");
    exit;
}

$is_logged_in = isset($_SESSION['user_id']);

$filtry = [
    'fraza'       => htmlspecialchars($_GET['fraza']       ?? ''),
    'kategoria'   => htmlspecialchars($_GET['kategoria']   ?? ''),
    'lokalizacja' => htmlspecialchars($_GET['lokalizacja'] ?? ''),
    'cena_min'    => htmlspecialchars($_GET['cena_min']    ?? ''),
    'cena_max'    => htmlspecialchars($_GET['cena_max']    ?? ''),
    'data_start'  => htmlspecialchars($_GET['data_start']  ?? ''),
    'data_end'    => htmlspecialchars($_GET['data_end']    ?? ''),
];
$url_filters = http_build_query($filtry);

if (!$is_logged_in) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
}

$err_msg = isset($_GET['err']) ? htmlspecialchars($_GET['err']) : '';
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoRent - <?= htmlspecialchars($auto['marka'] . ' ' . $auto['model']) ?></title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/car-detail.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>

<body class="auth-body">

    <a href="flota.php?<?= $url_filters ?>" class="back-to-home car-detail-back">
        <span class="material-symbols-outlined">arrow_back</span>
        Wróć do floty
    </a>

    <div class="auth-card car-detail-card">
        <h2><?= htmlspecialchars($auto['marka']) ?> <span><?= htmlspecialchars($auto['model']) ?></span></h2>
        <p class="auth-subtitle">Szczegóły pojazdu i konfiguracja rezerwacji</p>

        <div class="detail-layout">

            <div class="detail-left">
                <img src="images/<?= htmlspecialchars($auto['zdjecie']) ?>" alt="<?= htmlspecialchars($auto['marka']) ?>" class="car-detail-img">

                <h4 class="detail-section-label">Specyfikacja pojazdu</h4>

                <div class="car-specs-grid">
                    <div class="spec-item">
                        <span class="material-symbols-outlined">calendar_today</span>
                        <span>Rok produkcji: <strong><?= htmlspecialchars($auto['rok_produkcji'] ?? '2023') ?></strong></span>
                    </div>
                    <div class="spec-item">
                        <span class="material-symbols-outlined">speed</span>
                        <span>Moc silnika: <strong><?= (int)$auto['moc'] ?> KM</strong></span>
                    </div>
                    <div class="spec-item">
                        <span class="material-symbols-outlined">auto_transmission</span>
                        <span>Skrzynia: <strong><?= htmlspecialchars($auto['skrzynia']) ?></strong></span>
                    </div>
                    <div class="spec-item">
                        <span class="material-symbols-outlined">door_front</span>
                        <span>Drzwi: <strong><?= (int)$auto['liczba_drzwi'] ?>-drzwiowy</strong></span>
                    </div>
                    <div class="spec-item full-row">
                        <span class="material-symbols-outlined">luggage</span>
                        <span>Bagażnik: <strong><?= htmlspecialchars($auto['pojemnosc_bagaznika'] ?? '400') ?> litrów</strong></span>
                    </div>
                </div>
            </div>

            <div class="detail-right">
                <?php if ($is_logged_in): ?>
                    <div class="booking-header">
                        <span id="dynamicPrice" class="booking-price"><?= number_format($auto['cena_za_dobe'], 0, ',', ' ') ?> zł</span>
                        <span id="priceLabel" class="booking-price-label"> / doba</span>
                    </div>

                    <div id="availabilityAlert" class="booking-alert <?= $err_msg ? 'visible' : '' ?>"><?= $err_msg ?></div>

                    <form action="proces_rezerwacji.php" method="POST" class="auth-form" id="realBookingForm">
                        <input type="hidden" name="samochod_id" id="ajaxCarId" value="<?= (int)$auto['id'] ?>">

                        <div class="booking-dates">
                            <div class="input-group">
                                <label class="booking-label">Data od</label>
                                <input type="date" name="data_start" id="fleetStart" class="auth-input booking-input" required value="<?= $filtry['data_start'] ?>">
                            </div>
                            <div class="input-group">
                                <label class="booking-label">Data do</label>
                                <input type="date" name="data_end" id="fleetEnd" class="auth-input booking-input" required value="<?= $filtry['data_end'] ?>">
                            </div>
                        </div>

                        <div class="filter-group booking-section">
                            <h4>Pakiet Ochrony</h4>
                            <div class="compact-row-container">
                                <label class="addon-label">
                                    <input type="radio" name="pakiet_ochrony" value="podstawowy" class="ajax-package" checked>
                                    <span>Podstawowy (+0 zł)</span>
                                </label>
                                <label class="addon-label">
                                    <input type="radio" name="pakiet_ochrony" value="premium" class="ajax-package">
                                    <span>Komfort (+40 zł)</span>
                                </label>
                                <label class="addon-label">
                                    <input type="radio" name="pakiet_ochrony" value="vip" class="ajax-package">
                                    <span>VIP (+80 zł)</span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-group booking-section">
                            <h4>Opcje Dodatkowe</h4>
                            <div class="compact-row-container">
                                <label class="addon-label">
                                    <input type="checkbox" name="dodatki[]" value="fotelik" class="ajax-addon">
                                    <span>Fotelik (+20 zł/dobę)</span>
                                </label>
                                <label class="addon-label">
                                    <input type="checkbox" name="dodatki[]" value="gps" class="ajax-addon">
                                    <span>Nawigacja GPS (+15 zł/dobę)</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" id="submitBookingBtn" class="auth-submit-btn booking-submit">Zarezerwuj teraz</button>
                    </form>
                <?php else: ?>
                    <div class="booking-auth-alert">
                        <span class="material-symbols-outlined lock-icon">lock</span>
                        <p>Rezerwacja pojazdów jest dostępna wyłącznie dla zarejestrowanych kierowców.</p>
                        <a href="logowanie.php" class="auth-submit-btn">Zaloguj się</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>