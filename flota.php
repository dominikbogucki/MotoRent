<?php
include 'includes/db.php';

$dzis_data = date('Y-m-d');

if (!empty($_GET['data_start']) && $_GET['data_start'] < $dzis_data) {
    $_GET['data_start'] = $dzis_data;
}

if (
    !empty($_GET['data_start']) && !empty($_GET['data_end'])
    && $_GET['data_end'] < $_GET['data_start']
) {
    $_GET['data_end'] = '';
}

$data_start_check = !empty($_GET['data_start']) ? $_GET['data_start'] : $dzis_data;
$data_end_check   = !empty($_GET['data_end'])   ? $_GET['data_end']   : $dzis_data;

$sql = "SELECT s.*,
       (SELECT COUNT(*) FROM wypozyczenia w
        JOIN historia_pojazdow h ON w.id_historii_samochodu = h.id
        WHERE h.oryginalne_id_auta = s.id
          AND w.status IN ('oczekuje', 'zatwierdzone')
          AND NOT (w.data_end < ? OR w.data_start > ?)
       ) AS kolizje_terminu
FROM samochody s WHERE 1=1";
$params = [$data_start_check, $data_end_check];

if (!empty($_GET['fraza'])) {
    $sql .= " AND (s.marka LIKE ? OR s.model LIKE ?)";
    $szukaj = '%' . trim($_GET['fraza']) . '%';
    $params[] = $szukaj;
    $params[] = $szukaj;
}

if (!empty($_GET['kategoria'])) {
    $sql .= " AND s.kategoria = ?";
    $params[] = $_GET['kategoria'];
}

if (!empty($_GET['lokalizacja'])) {
    $sql .= " AND s.lokalizacja = ?";
    $params[] = $_GET['lokalizacja'];
}

if (!empty($_GET['cena_min'])) {
    $sql .= " AND s.cena_za_dobe >= ?";
    $params[] = (float)$_GET['cena_min'];
}
if (!empty($_GET['cena_max'])) {
    $sql .= " AND s.cena_za_dobe <= ?";
    $params[] = (float)$_GET['cena_max'];
}

$sql .= " ORDER BY (s.dostepny = 1 AND kolizje_terminu = 0) DESC, s.marka ASC, s.model ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$samochody = $stmt->fetchAll();

$forward_filters = http_build_query([
    'fraza'       => $_GET['fraza']       ?? '',
    'kategoria'   => $_GET['kategoria']   ?? '',
    'lokalizacja' => $_GET['lokalizacja'] ?? '',
    'cena_min'    => $_GET['cena_min']    ?? '',
    'cena_max'    => $_GET['cena_max']    ?? '',
    'data_start'  => $_GET['data_start']  ?? '',
    'data_end'    => $_GET['data_end']    ?? '',
]);

include 'includes/header.php';
?>

<main class="fleet-page-wrapper">
    <div class="fleet-main-container">

        <aside class="filters-sidebar">
            <h3>Filtruj <span>Flotę</span></h3>
            <form id="filterForm" action="flota.php" method="GET">

                <div class="filter-group">
                    <h4><span class="material-symbols-outlined">search</span>Wyszukiwarka</h4>
                    <input type="text" name="fraza" placeholder="Marka lub model..." class="filter-input-text" value="<?= htmlspecialchars($_GET['fraza'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <h4><span class="material-symbols-outlined">directions_car</span>Kategoria</h4>
                    <select name="kategoria" class="filter-select">
                        <option value="">Wszystkie kategorie</option>
                        <?php
                        $kategorie = [
                            'premium'      => 'Sportowe / Premium',
                            'suv'          => 'SUV / Terenowe',
                            'ekonomiczne'  => 'Miejskie / Ekonomiczne',
                            'elektryczne'  => 'Elektryczne',
                        ];
                        foreach ($kategorie as $val => $label):
                            $sel = (($_GET['kategoria'] ?? '') === $val) ? 'selected' : '';
                        ?>
                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h4><span class="material-symbols-outlined">location_on</span>Lokalizacja</h4>
                    <select name="lokalizacja" class="filter-select">
                        <option value="">Wszystkie miasta</option>
                        <?php
                        $miasta = [
                            'sokolow'  => 'Sokołów Podlaski',
                            'siedlce'  => 'Siedlce',
                            'warszawa' => 'Warszawa',
                        ];
                        foreach ($miasta as $val => $label):
                            $sel = (($_GET['lokalizacja'] ?? '') === $val) ? 'selected' : '';
                        ?>
                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h4><span class="material-symbols-outlined">payments</span>Cena za dobę (zł)</h4>
                    <div class="price-inputs">
                        <input type="number" name="cena_min" placeholder="Od" min="0" value="<?= htmlspecialchars($_GET['cena_min'] ?? '') ?>">
                        <input type="number" name="cena_max" placeholder="Do" min="0" value="<?= htmlspecialchars($_GET['cena_max'] ?? '') ?>">
                    </div>
                </div>

                <div class="filter-group">
                    <h4><span class="material-symbols-outlined">calendar_today</span>Termin wynajmu</h4>
                    <input type="date" name="data_start" id="fleetStart" class="filter-input-text" value="<?= htmlspecialchars($_GET['data_start'] ?? '') ?>">
                    <input type="date" name="data_end" id="fleetEnd" class="filter-input-text" value="<?= htmlspecialchars($_GET['data_end'] ?? '') ?>">
                </div>

                <button type="submit" class="filter-submit-btn">Zastosuj filtry</button>
                <a href="flota.php" class="clear-filters-link">Wyczyść filtry</a>
            </form>
        </aside>

        <section class="fleet-grid-wrapper">
            <div class="flota-grid">

                <?php if (empty($samochody)): ?>
                    <div class="no-cars-msg">
                        <span class="material-symbols-outlined">filter_alt_off</span>
                        <span class="msg-text">Brak samochodów spełniających kryteria</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($samochody as $auto):
                        $is_available = ((int)$auto['dostepny'] === 1 && (int)$auto['kolizje_terminu'] === 0);
                    ?>
                        <div class="car-card <?= !$is_available ? 'car-card-unavailable' : '' ?>">
                            <div class="car-image">
                                <img src="images/<?= htmlspecialchars($auto['zdjecie']) ?>" alt="<?= htmlspecialchars($auto['marka'] . ' ' . $auto['model']) ?>">
                            </div>
                            <div class="car-info">
                                <div class="car-status-badges">
                                    <span class="car-category"><?= ucfirst(htmlspecialchars($auto['kategoria'])) ?></span>

                                    <?php if ($is_available): ?>
                                        <span class="status-badge status-available">Dostępny</span>
                                    <?php else: ?>
                                        <span class="status-badge status-rented">Wypożyczony</span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="car-name"><?= htmlspecialchars($auto['marka']) ?> <span><?= htmlspecialchars($auto['model']) ?></span></h3>

                                <div class="car-specs-grid">
                                    <div class="spec-item" title="Rodzaj paliwa">
                                        <span class="material-symbols-outlined">local_gas_station</span>
                                        <span><?= htmlspecialchars($auto['paliwo']) ?></span>
                                    </div>
                                    <div class="spec-item" title="Moc silnika">
                                        <span class="material-symbols-outlined">speed</span>
                                        <span><?= (int)$auto['moc'] ?> KM</span>
                                    </div>
                                    <div class="spec-item" title="Skrzynia biegów">
                                        <span class="material-symbols-outlined">auto_transmission</span>
                                        <span><?= htmlspecialchars($auto['skrzynia']) ?></span>
                                    </div>
                                    <div class="spec-item" title="Liczba drzwi">
                                        <span class="material-symbols-outlined">door_front</span>
                                        <span><?= (int)$auto['liczba_drzwi'] ?>-drzwiowy</span>
                                    </div>
                                </div>

                                <div class="car-footer">
                                    <div class="car-price">
                                        <span class="price-value"><?= number_format($auto['cena_za_dobe'], 0, ',', ' ') ?> zł</span>
                                        <span class="price-period">/ doba</span>
                                    </div>

                                    <?php if ($is_available): ?>
                                        <a href="samochod.php?id=<?= $auto['id'] ?>&<?= $forward_filters ?>" class="car-btn">Szczegóły</a>
                                    <?php else: ?>
                                        <button class="car-btn car-btn-disabled" disabled>Niedostępny</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

    </div>
</main>

<button id="backToTop" class="back-to-top-btn">
    <span class="material-symbols-outlined">arrow_upward</span>
</button>

<?php include 'includes/footer.php'; ?>