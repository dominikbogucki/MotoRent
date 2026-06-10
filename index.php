<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<main>
    <section id="hero-section" class="hero-section">

        <div class="hero-content">
            <h1>Odkryj wolność na własnych <span>warunkach</span></h1>
            <p>Nowoczesna flota, przejrzyste zasady i rezerwacja online bez zbędnych formalności.</p>
        </div>
        <div class="hero-bottom-bar">
            <form action="flota.php" method="GET" class="hero-search-bar">

                <div class="search-group">
                    <label>Kategoria</label>
                    <select name="kategoria">
                        <option value="" disabled selected>Wybierz kategorię...</option>
                        <option value="premium">Sportowe / Premium</option>
                        <option value="suv">SUV / Terenowe</option>
                        <option value="ekonomiczne">Miejskie / Ekonomiczne</option>
                        <option value="elektryczne">Elektryczne</option>
                    </select>
                </div>

                <div class="search-divider"></div>

                <div class="search-group">
                    <label>Lokalizacja</label>
                    <select name="lokalizacja">
                        <option value="" disabled selected>Wybierz miasto...</option>
                        <option value="sokolow">Sokołów Podlaski</option>
                        <option value="siedlce">Siedlce</option>
                        <option value="warszawa">Warszawa</option>
                    </select>
                </div>

                <div class="search-divider"></div>

                <div class="search-group">
                    <label>Data od</label>
                    <input type="date" name="data_start" id="indexStart">
                </div>

                <div class="search-divider"></div>

                <div class="search-group">
                    <label>Data do</label>
                    <input type="date" name="data_end" id="indexEnd">
                </div>

                <div class="search-divider"></div>

                <button type="submit" class="search-submit-btn">Szukaj</button>
            </form>
        </div>

    </section>

    <section class="flota-section">
        <div class="flota-container">

            <h2 class="flota-title">Nasza <span>Flota</span></h2>
            <p class="flota-subtitle">Wybierz jeden z naszych najpopularniejszych modeli i ruszaj w drogę</p>

            <div class="flota-grid">

                <?php
                $dzis = date('Y-m-d');
                $stmt = $pdo->prepare("
                    SELECT s.* FROM samochody s
                    WHERE s.dostepny = 1
                    AND NOT EXISTS (
                        SELECT 1 FROM wypozyczenia w
                        JOIN historia_pojazdow h ON w.id_historii_samochodu = h.id
                        WHERE h.oryginalne_id_auta = s.id
                        AND w.status IN ('oczekuje', 'zatwierdzone')
                        AND ? BETWEEN w.data_start AND w.data_end
                    )
                    ORDER BY RAND() LIMIT 3
                ");
                $stmt->execute([$dzis]);
                $samochody = $stmt->fetchAll();

                if (empty($samochody)): ?>
                    <div class="no-cars-msg">
                        <span class="material-symbols-outlined">directions_car</span>
                        <span class="msg-text">Brak dostępnych samochodów we flocie</span>
                    </div>
                <?php else: ?>
                <?php foreach ($samochody as $auto): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <img src="images/<?= $auto['zdjecie']; ?>" alt="<?= $auto['marka'] . ' ' . $auto['model']; ?>">
                        </div>
                        <div class="car-info">
                            <div class="car-status-badges">
                                <span class="car-category"><?= ucfirst($auto['kategoria']); ?></span>

                                <?php if ((int)$auto['dostepny'] === 1): ?>
                                    <span class="status-badge status-available">Dostępny</span>
                                <?php else: ?>
                                    <span class="status-badge status-rented">Wypożyczony</span>
                                <?php endif; ?>
                            </div>

                            <h3 class="car-name"><?= $auto['marka']; ?> <span><?= $auto['model']; ?></span></h3>

                            <div class="car-specs-grid">
                                <div class="spec-item" title="Rodzaj paliwa">
                                    <span class="material-symbols-outlined">local_gas_station</span>
                                    <span><?= $auto['paliwo']; ?></span>
                                </div>
                                <div class="spec-item" title="Moc silnika">
                                    <span class="material-symbols-outlined">speed</span> <span><?= $auto['moc']; ?> KM</span>
                                </div>
                                <div class="spec-item" title="Skrzynia biegów">
                                    <span class="material-symbols-outlined">auto_transmission</span>
                                    <span><?= $auto['skrzynia']; ?></span>
                                </div>
                                <div class="spec-item" title="Liczba drzwi">
                                    <span class="material-symbols-outlined">door_front</span>
                                    <span><?= $auto['liczba_drzwi']; ?>-drzwiowy</span>
                                </div>
                            </div>

                            <div class="car-footer">
                                <div class="car-price">
                                    <span class="price-value"><?= number_format($auto['cena_za_dobe'], 0, ',', ' '); ?> zł</span>
                                    <span class="price-period">/ doba</span>
                                </div>
                                <a href="samochod.php?id=<?= $auto['id']; ?>" class="car-btn">Szczegóły</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
            <div class="flota-more-wrapper">
                <a href="flota.php" class="flota-more-btn">Zobacz pełną flotę</a>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="features-container">

            <h2 class="features-title">Dlaczego <span>MotoRent</span>?</h2>
            <p class="features-subtitle">Standardy premium, które redefiniują komfort wynajmu samochodów</p>

            <div class="features-grid">

                <div class="feature-box">
                    <div class="feature-icon">
                        <span class="material-symbols-outlined google-icon">verified_user</span>
                    </div>
                    <h3>Przejrzyste zasady</h3>
                    <p>Pełne ubezpieczenie w cenie wynajmu. Jasne umowy bez drobnego druku i ukrytych opłat
                        dodatkowych.</p>
                </div>

                <div class="feature-box">
                    <div class="feature-icon">
                        <span class="material-symbols-outlined google-icon">bolt</span>
                    </div>
                    <h3>Błyskawiczny proces</h3>
                    <p>Ograniczamy formalności do minimum. Intuicyjny system rezerwacji online pozwala zamknąć
                        proces w kilka minut.</p>
                </div>

                <div class="feature-box">
                    <div class="feature-icon">
                        <span class="material-symbols-outlined google-icon">time_to_leave</span>
                    </div>
                    <h3>Flota Premium</h3>
                    <p>Wszystkie nasze pojazdy przechodzą rygorystyczne testy jakości. Gwarantujemy idealny stan
                        techniczny i wizualny.</p>
                </div>

            </div>
        </div>
    </section>

    <section class="reviews-section">
        <div class="reviews-container">

            <h2 class="reviews-title">Co mówią nasi <span>klienci</span>?</h2>
            <p class="reviews-subtitle">Opinie kierowców, którzy zaufali standardom MotoRent</p>

            <div class="reviews-window">
                <div class="reviews-track" id="reviewsTrack">

                    <?php
                    $stmt = $pdo->query("SELECT * FROM opinie ORDER BY data_dodania DESC");
                    $opinie = $stmt->fetchAll();

                    foreach ($opinie as $opinia):
                    ?>
                        <div class="review-card">
                            <div class="review-stars">
                                <?php for ($i = 0; $i < $opinia['ocena']; $i++): ?>
                                    <span class="material-symbols-outlined">star</span>
                                <?php endfor; ?>
                            </div>
                            <p class="review-text">"<?= htmlspecialchars($opinia['tresc']); ?>"</p>
                            <div class="review-author">
                                <div class="author-info">
                                    <h4><?= htmlspecialchars($opinia['autor']); ?></h4>
                                    <span>Wynajmował: <?= htmlspecialchars($opinia['samochod']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>

        </div>
    </section>
</main>
<button id="backToTop" class="back-to-top-btn">
    <span class="material-symbols-outlined">arrow_upward</span>
</button>
<?php
include 'includes/footer.php';
?>