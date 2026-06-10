<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt_user = $pdo->prepare("SELECT * FROM uzytkownicy WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

if (!$user) {
    header("Location: wyloguj.php");
    exit;
}

$stmt_rez = $pdo->prepare("
    SELECT w.*, h.marka, h.model, h.cena_za_dobe_wtedy
    FROM wypozyczenia w
    JOIN historia_pojazdow h ON w.id_historii_samochodu = h.id
    WHERE w.id_uzytkownika = ?
    ORDER BY w.id DESC
");
$stmt_rez->execute([$user_id]);
$rezerwacje = $stmt_rez->fetchAll();

$komunikat = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';

function pakietInfo(string $pakiet): array
{
    return match ($pakiet) {
        'premium' => ['Komfort',     'cell-pakiet-premium'],
        'vip'     => ['VIP',         'cell-pakiet-vip'],
        default   => ['Podstawowy',  'cell-pakiet-podstawowy'],
    };
}

function statusInfo(string $status): array
{
    return match ($status) {
        'zatwierdzone' => ['Zatwierdzone', 'status-zatwierdzone'],
        'zakonczone'   => ['Zakończone',   'status-zakonczone'],
        'anulowane'    => ['Anulowane',    'status-anulowane'],
        default        => ['Oczekuje',     'status-oczekuje'],
    };
}

include 'includes/header.php';
?>

<main class="account-page-wrapper">
    <div class="account-container">

        <?php if ($komunikat): ?>
            <div class="alert-success-banner">
                <span class="material-symbols-outlined">check_circle</span>
                <p><?= $komunikat ?></p>
            </div>
        <?php endif; ?>

        <h1 class="account-page-title">Panel <span class="accent">Klienta</span></h1>
        <p class="account-page-subtitle">Witaj w swoim panelu. Możesz tutaj zarządzać swoimi danymi oraz sprawdzać historię swoich rezerwacji.</p>

        <div class="account-grid">

            <section class="profile-card">
                <div class="profile-avatar-large">
                    <span class="material-symbols-outlined">account_circle</span>
                </div>
                <h3><?= htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']) ?></h3>
                <span class="role-badge"><?= $user['rola'] === 'admin' ? 'Administrator' : 'Kierowca' ?></span>

                <div class="profile-details">
                    <div class="profile-detail-item">
                        <label>Adres e-mail</label>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <div class="profile-detail-item">
                        <label>Data urodzenia</label>
                        <p><?= date('d.m.Y', strtotime($user['data_urodzenia'])) ?></p>
                    </div>
                    <div class="profile-detail-item">
                        <label>Konto od</label>
                        <p><?= date('d.m.Y H:i', strtotime($user['data_rejestracji'])) ?></p>
                    </div>
                </div>
            </section>

            <div class="dashboard-main-column">
                <section class="reservations-card">
                    <h2>Moje <span>Rezerwacje</span></h2>

                    <?php if (empty($rezerwacje)): ?>
                        <div class="empty-booking-msg">
                            <span class="material-symbols-outlined">no_backpack</span>
                            <p>Nie posiadasz jeszcze żadnych rezerwacji w naszym systemie.</p>
                            <a href="flota.php" class="book-now-btn">Przeglądaj flotę i zarezerwuj</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="reservations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pojazd</th>
                                        <th>Pakiet Ochrony</th>
                                        <th>Od - Do</th>
                                        <th>Koszt Całkowity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rezerwacje as $r): ?>
                                        <?php
                                        [$pakiet_name, $pakiet_class] = pakietInfo($r['pakiet_ochrony']);
                                        [$status_name, $status_class] = statusInfo($r['status']);
                                        ?>
                                        <tr>
                                            <td><strong>#<?= $r['id'] ?></strong></td>
                                            <td>
                                                <span class="cell-car-brand"><?= htmlspecialchars($r['marka']) ?></span><br>
                                                <span class="cell-car-model"><?= htmlspecialchars($r['model']) ?></span>
                                            </td>
                                            <td>
                                                <span class="cell-pakiet <?= $pakiet_class ?>"><?= $pakiet_name ?></span>
                                            </td>
                                            <td>
                                                <small class="cell-date-start"><?= date('d.m.Y', strtotime($r['data_start'])) ?></small><br>
                                                <small class="cell-date-end"><?= date('d.m.Y', strtotime($r['data_end'])) ?></small>
                                            </td>
                                            <td><strong class="cell-price"><?= number_format($r['koszt_calkowity'], 0, ',', ' ') ?> zł</strong></td>
                                            <td>
                                                <span class="badge-status <?= $status_class ?>"><?= $status_name ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>