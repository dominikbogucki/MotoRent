<?php
include 'includes/db.php';
include 'includes/header.php';

$liczba_aut       = (int)$pdo->query("SELECT COUNT(*) FROM samochody")->fetchColumn();
$liczba_klientow  = (int)$pdo->query("SELECT COUNT(*) FROM uzytkownicy WHERE rola = 'klient'")->fetchColumn();
$liczba_kategorii = 4;
$liczba_lokalizacji = 3;

$regulamin = [
    ['Rezerwacja online',
     'Rezerwacje przyjmujemy wyłącznie przez nasz system - wystarczy założyć darmowe konto klienta.'],

    ['Wymagany wiek',
     'Aby zarejestrować konto i wypożyczyć pojazd, musisz mieć ukończone 18 lat.'],

    ['Zatwierdzenie rezerwacji',
     'Każda rezerwacja otrzymuje status "Oczekuje" i jest następnie weryfikowana oraz zatwierdzana przez obsługę.'],

    ['Pakiet ochrony',
     'Do każdego wynajmu wybierasz jeden z trzech pakietów: Podstawowy (w cenie), Komfort (+40 zł/doba) lub VIP (+80 zł/doba).'],

    ['Opcje dodatkowe',
     'Możesz doliczyć fotelik dziecięcy (+20 zł/doba) oraz nawigację GPS (+15 zł/doba).'],

    ['Anulowanie i zmiana',
     'Aby anulować lub zmienić termin rezerwacji, skontaktuj się z obsługą - status zostanie zaktualizowany przez administratora.'],
];

$faq = [
    ['Jak działa rezerwacja?',
     'Wybierasz samochód z floty, podajesz termin, pakiet ochrony i ewentualne dodatki. Po wysłaniu formularza rezerwacja trafia ze statusem "Oczekuje" do obsługi, która ją zatwierdza.'],

    ['Jak wyliczana jest cena?',
     'Cena za całość = (cena za dobę + dopłata za pakiet ochrony + ewentualne dodatki) × liczba dni wynajmu. Wszystko widzisz na bieżąco w konfiguratorze na karcie auta.'],

    ['Czym różnią się pakiety ochrony?',
     'Pakiet Podstawowy jest wliczony w cenę. Komfort (+40 zł/doba) i VIP (+80 zł/doba) to rozszerzona ochrona o wyższe limity.'],

    ['Jak sprawdzić status mojej rezerwacji?',
     'Po zalogowaniu wejdź w "Panel Klienta" - znajdziesz tam listę wszystkich swoich rezerwacji wraz z ich aktualnym statusem (Oczekuje / Zatwierdzone / Zakończone / Anulowane).'],

    ['Czy mogę anulować rezerwację samodzielnie?',
     'Anulowanie odbywa się przez kontakt z obsługą - administrator zmieni status rezerwacji, a pojazd zostanie zwolniony dla innych klientów.'],

    ['Gdzie odbieram samochód?',
     'Aktualnie działamy w trzech lokalizacjach: Sokołów Podlaski, Siedlce i Warszawa. Lokalizację możesz przefiltrować na podstronie "Nasza Flota".'],
];
?>

<main>

    <section class="about-intro">
        <div class="about-container">
            <h1 class="about-section-title">O <span>MotoRent</span></h1>
            <p class="about-section-subtitle">Prosta i przejrzysta wypożyczalnia samochodów online</p>

            <p class="about-intro-text">
                MotoRent to internetowy system wynajmu samochodów. Umożliwiamy szybką rezerwację pojazdu
                z dowolnej z naszych lokalizacji, wybór pakietu ochrony oraz opcji dodatkowych - wszystko
                z poziomu jednego konta klienta. Nasz cel to maksymalna prostota: bez papierów, bez kolejek,
                bez ukrytych kosztów.
            </p>

            <div class="about-stats">
                <div class="about-stat">
                    <span class="about-stat-number"><?= $liczba_aut ?></span>
                    <span class="about-stat-label">Pojazdów we flocie</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-number"><?= $liczba_klientow ?></span>
                    <span class="about-stat-label">Zarejestrowanych klientów</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-number"><?= $liczba_kategorii ?></span>
                    <span class="about-stat-label">Kategorie pojazdów</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-number"><?= $liczba_lokalizacji ?></span>
                    <span class="about-stat-label">Lokalizacje odbioru</span>
                </div>
            </div>
        </div>
    </section>

    <section class="about-regulamin">
        <div class="about-container">
            <h2 class="about-section-title">Krótki <span>Regulamin</span></h2>
            <p class="about-section-subtitle">Najważniejsze zasady korzystania z serwisu MotoRent</p>

            <ol class="regulamin-list">
                <?php foreach ($regulamin as [$tytul, $tresc]): ?>
                    <li>
                        <strong><?= htmlspecialchars($tytul) ?></strong>
                        <?= htmlspecialchars($tresc) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="about-faq">
        <div class="about-container">
            <h2 class="about-section-title">Najczęściej zadawane <span>pytania</span></h2>
            <p class="about-section-subtitle">Kliknij pytanie, aby zobaczyć odpowiedź</p>

            <div class="faq-list">
                <?php foreach ($faq as [$pytanie, $odpowiedz]): ?>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span><?= htmlspecialchars($pytanie) ?></span>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="faq-answer">
                            <p><?= htmlspecialchars($odpowiedz) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</main>

<button id="backToTop" class="back-to-top-btn">
    <span class="material-symbols-outlined">arrow_upward</span>
</button>

<?php include 'includes/footer.php'; ?>
