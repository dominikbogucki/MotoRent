<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';

if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$total_cars      = $pdo->query("SELECT COUNT(*) FROM samochody")->fetchColumn();
$total_users     = $pdo->query("SELECT COUNT(*) FROM uzytkownicy WHERE rola = 'klient'")->fetchColumn();
$total_earnings  = $pdo->query("SELECT SUM(koszt_calkowity) FROM wypozyczenia WHERE status IN ('zatwierdzone', 'zakonczone')")->fetchColumn() ?? 0;
$pending_rentals = $pdo->query("SELECT COUNT(*) FROM wypozyczenia WHERE status = 'oczekuje'")->fetchColumn();

$komunikat = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
$blad      = isset($_GET['err']) ? htmlspecialchars($_GET['err']) : '';

$view = $_GET['view'] ?? 'dodaj';

function menuActive(string $view, array $matches): string
{
    return in_array($view, $matches, true) ? 'active' : '';
}

$titles = [
    'auta'        => 'Zarządzanie Flotą',
    'rezerwacje'  => 'Historia Wypożyczeń',
    'uzytkownicy' => 'Zarządzanie Użytkownikami',
];

include 'includes/header.php';
?>

<main class="admin-container">
    <h1 class="admin-page-title">Panel <span class="accent">Administratora</span></h1>
    <p class="admin-page-subtitle">Zarządzanie flotą, rezerwacjami oraz użytkownikami systemu MotoRent.</p>

    <div class="stats-grid">
        <div class="stat-box">
            <h4>Pojazdy we flocie</h4>
            <p><?= $total_cars ?></p>
        </div>
        <div class="stat-box">
            <h4>Zarejestrowani Klienci</h4>
            <p><?= $total_users ?></p>
        </div>
        <div class="stat-box">
            <h4>Suma obrotu</h4>
            <p><?= number_format($total_earnings, 0, ',', ' ') ?> zł</p>
        </div>
        <div class="stat-box highlight">
            <h4>Nowe zamówienia</h4>
            <p><?= $pending_rentals ?></p>
        </div>
    </div>

    <?php if ($komunikat): ?><div class="alert-success"><?= $komunikat ?></div><?php endif; ?>
    <?php if ($blad): ?><div class="alert-danger"><?= $blad ?></div><?php endif; ?>

    <div class="admin-grid">
        <div class="admin-menu">
            <a href="admin.php?view=dodaj" class="<?= menuActive($view, ['dodaj']) ?>">
                <span class="material-symbols-outlined">add_box</span>Dodaj nowy pojazd
            </a>
            <a href="admin.php?view=auta" class="<?= menuActive($view, ['auta', 'edytuj']) ?>">
                <span class="material-symbols-outlined">directions_car</span>Zarządzaj flotą
            </a>
            <a href="admin.php?view=rezerwacje" class="<?= menuActive($view, ['rezerwacje']) ?>">
                <span class="material-symbols-outlined">history</span>Historia wypożyczeń
            </a>
            <a href="admin.php?view=uzytkownicy" class="<?= menuActive($view, ['uzytkownicy']) ?>">
                <span class="material-symbols-outlined">group</span>Użytkownicy
            </a>
        </div>

        <div class="admin-card">

            <?php if (in_array($view, ['auta', 'rezerwacje', 'uzytkownicy'])): ?>
                <div class="admin-view-header">
                    <h2><?= $titles[$view] ?></h2>
                    <input type="text" id="adminSearchInput" class="admin-search-input" placeholder="Szukaj w tabeli...">
                </div>
            <?php endif; ?>

            <?php if ($view === 'dodaj'): ?>
                <h2 class="admin-section-title">Dodaj samochód do floty</h2>
                <form action="admin_akcje.php?akcja=dodaj_auto" method="POST" enctype="multipart/form-data">
                    <div class="admin-form-grid">
                        <div>
                            <label>Marka</label>
                            <input type="text" name="marka" class="admin-input" required placeholder="np. Audi">

                            <label>Model</label>
                            <input type="text" name="model" class="admin-input" required placeholder="np. RS3">

                            <label>Kategoria</label>
                            <select name="kategoria" class="admin-select" required>
                                <option value="premium">Sportowe / Premium</option>
                                <option value="suv">SUV / Terenowe</option>
                                <option value="ekonomiczne">Miejskie / Ekonomiczne</option>
                                <option value="elektryczne">Elektryczne</option>
                            </select>

                            <label>Cena za dobę (zł)</label>
                            <input type="number" step="0.01" name="cena_za_dobe" class="admin-input" required placeholder="np. 250.00">

                            <label>Lokalizacja</label>
                            <select name="lokalizacja" class="admin-select" required>
                                <option value="warszawa">Warszawa</option>
                                <option value="siedlce">Siedlce</option>
                                <option value="sokolow">Sokołów Podlaski</option>
                            </select>
                        </div>
                        <div>
                            <label>Rodzaj Paliwa</label>
                            <input type="text" name="paliwo" class="admin-input" required placeholder="np. Benzyna">

                            <label>Moc (KM)</label>
                            <input type="number" name="moc" class="admin-input" required placeholder="np. 150">

                            <label>Skrzynia biegów</label>
                            <input type="text" name="skrzynia" class="admin-input" required placeholder="np. Automatyczna">

                            <label>Liczba drzwi</label>
                            <input type="number" name="liczba_drzwi" class="admin-input" required placeholder="np. 5">

                            <label>Rok produkcji</label>
                            <input type="number" name="rok_produkcji" class="admin-input" required placeholder="np. 2023">

                            <input type="hidden" name="pojemnosc_bagaznika" value="400">

                            <label class="highlight">Zdjęcie pojazdu</label>
                            <input type="file" name="zdjecie" class="admin-input" required>
                        </div>
                    </div>
                    <button type="submit" class="filter-submit-btn">Dodaj do bazy</button>
                </form>

            <?php elseif ($view === 'auta'):
                $auta = $pdo->query("SELECT * FROM samochody ORDER BY id DESC")->fetchAll();
            ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Auto</th>
                                <th>Kategoria</th>
                                <th>Cena</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auta as $a): ?>
                                <tr>
                                    <td><img src="images/<?= htmlspecialchars($a['zdjecie']) ?>" class="admin-thumb" alt=""></td>
                                    <td><strong><?= htmlspecialchars($a['marka']) ?></strong> <?= htmlspecialchars($a['model']) ?> (<?= (int)$a['rok_produkcji'] ?>)</td>
                                    <td><?= htmlspecialchars($a['kategoria']) ?></td>
                                    <td><?= $a['cena_za_dobe'] ?> zł</td>
                                    <td>
                                        <?php if ((int)$a['dostepny'] === 1): ?>
                                            <span class="status-free">Wolny</span>
                                        <?php else: ?>
                                            <span class="status-busy">Zajęty</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin.php?view=edytuj&id=<?= $a['id'] ?>" class="btn-edit">Edytuj</a>
                                        <a href="admin_akcje.php?akcja=usun_auto&id=<?= $a['id'] ?>" class="btn-delete"
                                            onclick="return confirm('Czy na pewno usunąć to auto permanentnie?')">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($view === 'edytuj'):
                $id_auta = (int)($_GET['id'] ?? 0);
                $stmt = $pdo->prepare("SELECT * FROM samochody WHERE id = ?");
                $stmt->execute([$id_auta]);
                $car = $stmt->fetch();
            ?>
                <?php if (!$car): ?>
                    <p>Pojazd nie istnieje.</p>
                <?php else: ?>
                    <h2 class="admin-section-title">Edytuj pojazd: <?= htmlspecialchars($car['marka'] . ' ' . $car['model']) ?></h2>
                    <form action="admin_akcje.php?akcja=edytuj_auto" method="POST">
                        <input type="hidden" name="id" value="<?= (int)$car['id'] ?>">
                        <div class="admin-form-grid">
                            <div>
                                <label>Marka</label>
                                <input type="text" name="marka" class="admin-input" required value="<?= htmlspecialchars($car['marka']) ?>">

                                <label>Model</label>
                                <input type="text" name="model" class="admin-input" required value="<?= htmlspecialchars($car['model']) ?>">

                                <label>Cena za dobę (zł)</label>
                                <input type="number" step="0.01" name="cena_za_dobe" class="admin-input" required value="<?= $car['cena_za_dobe'] ?>">

                                <label>Status dostępności</label>
                                <select name="dostepny" class="admin-select">
                                    <option value="1" <?= (int)$car['dostepny'] === 1 ? 'selected' : '' ?>>Dostępny</option>
                                    <option value="0" <?= (int)$car['dostepny'] === 0 ? 'selected' : '' ?>>Niedostępny</option>
                                </select>
                            </div>
                            <div>
                                <label>Moc (KM)</label>
                                <input type="number" name="moc" class="admin-input" required value="<?= (int)$car['moc'] ?>">

                                <label>Skrzynia biegów</label>
                                <input type="text" name="skrzynia" class="admin-input" required value="<?= htmlspecialchars($car['skrzynia']) ?>">

                                <label>Rok produkcji</label>
                                <input type="number" name="rok_produkcji" class="admin-input" required value="<?= (int)$car['rok_produkcji'] ?>">
                            </div>
                        </div>
                        <button type="submit" class="filter-submit-btn">Zapisz zmiany</button>
                    </form>
                <?php endif; ?>

            <?php elseif ($view === 'rezerwacje'):
                $rezerwacje = $pdo->query("
                    SELECT w.*, hu.email, hu.imie, hu.nazwisko, h.marka, h.model
                    FROM wypozyczenia w
                    JOIN historia_uzytkownikow hu ON w.id_historii_uzytkownika = hu.id
                    JOIN historia_pojazdow h ON w.id_historii_samochodu = h.id
                    ORDER BY w.id DESC
                ")->fetchAll();
            ?>
                <div class="table-responsive">
                    <table class="admin-table admin-reservations-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Klient</th>
                                <th>Samochód / Pakiet</th>
                                <th>Termin</th>
                                <th>Koszt</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rezerwacje as $r):
                                $pakiet_klasa = 'pakiet-podstawowy';
                                $pakiet_name  = 'Podstawowy';
                                if ($r['pakiet_ochrony'] === 'premium') {
                                    $pakiet_klasa = 'pakiet-premium';
                                    $pakiet_name = 'Komfort';
                                } elseif ($r['pakiet_ochrony'] === 'vip') {
                                    $pakiet_klasa = 'pakiet-vip';
                                    $pakiet_name = 'VIP';
                                }
                            ?>
                                <tr>
                                    <td>#<?= (int)$r['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($r['imie'] . ' ' . $r['nazwisko']) ?></strong><br>
                                        <small class="client-meta">
                                            <?= htmlspecialchars($r['email']) ?>
                                            <?php if ($r['id_uzytkownika'] === null): ?>
                                                <br><span class="client-deleted-tag">Konto Usunięte</span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($r['marka'] . ' ' . $r['model']) ?></strong><br>
                                        <span class="pakiet-label <?= $pakiet_klasa ?>">Pakiet: <?= $pakiet_name ?></span>
                                    </td>
                                    <td>
                                        <small class="date-light"><?= date('d.m.Y', strtotime($r['data_start'])) ?></small><br>
                                        <small class="date-dim"><?= date('d.m.Y', strtotime($r['data_end'])) ?></small>
                                    </td>
                                    <td><strong class="res-cost"><?= $r['koszt_calkowity'] ?> zł</strong></td>
                                    <td><span class="res-status res-status-<?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                                    <td>
                                        <?php if ($r['status'] === 'oczekuje'): ?>
                                            <a href="admin_akcje.php?akcja=zmien_status&id=<?= $r['id'] ?>&status=zatwierdzone" class="btn-edit">Zatwierdź</a>
                                            <a href="admin_akcje.php?akcja=zmien_status&id=<?= $r['id'] ?>&status=anulowane" class="btn-delete">Anuluj</a>
                                        <?php elseif ($r['status'] === 'zatwierdzone'): ?>
                                            <a href="admin_akcje.php?akcja=zmien_status&id=<?= $r['id'] ?>&status=zakonczone" class="btn-edit">Zakończ</a>
                                            <a href="admin_akcje.php?akcja=zmien_status&id=<?= $r['id'] ?>&status=anulowane" class="btn-delete">Anuluj</a>
                                        <?php else: ?>
                                            <span class="date-dim">Brak akcji</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($view === 'uzytkownicy'):
                $userzy = $pdo->query("SELECT * FROM uzytkownicy ORDER BY id DESC")->fetchAll();
            ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imię i Nazwisko</th>
                                <th>Email</th>
                                <th>Rola</th>
                                <th>Data rejestracji</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userzy as $u): ?>
                                <tr>
                                    <td>#<?= (int)$u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['imie'] . ' ' . $u['nazwisko']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="<?= $u['rola'] === 'admin' ? 'role-admin' : '' ?>"><?= htmlspecialchars($u['rola']) ?></td>
                                    <td><?= $u['data_rejestracji'] ?></td>
                                    <td>
                                        <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                                            <span class="date-dim">Zalogowany</span>
                                        <?php else: ?>
                                            <a href="admin_akcje.php?akcja=usun_uzytkownika&id=<?= $u['id'] ?>" class="btn-delete"
                                                onclick="return confirm('Usunąć tego użytkownika? Aktywne rezerwacje zostaną anulowane.')">Usuń</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('#adminSearchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.admin-table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>