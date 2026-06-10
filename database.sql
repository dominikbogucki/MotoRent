CREATE TABLE IF NOT EXISTS `uzytkownicy` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `imie` VARCHAR(50) NOT NULL,
  `nazwisko` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `data_urodzenia` DATE NOT NULL,
  `haslo` VARCHAR(255) NOT NULL,
  `rola` ENUM('klient', 'admin') DEFAULT 'klient',
  `data_rejestracji` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `samochody` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `marka` VARCHAR(50) NOT NULL,
  `model` VARCHAR(50) NOT NULL,
  `kategoria` ENUM('premium', 'suv', 'ekonomiczne', 'elektryczne') NOT NULL,
  `cena_za_dobe` DECIMAL(10,2) NOT NULL,
  `lokalizacja` ENUM('sokolow', 'siedlce', 'warszawa') NOT NULL,
  `zdjecie` VARCHAR(255) NOT NULL,
  `dostepny` TINYINT(1) DEFAULT 1,
  `paliwo` VARCHAR(50) NOT NULL DEFAULT 'Benzyna',
  `moc` INT NOT NULL DEFAULT 150,
  `skrzynia` VARCHAR(50) NOT NULL DEFAULT 'Automatyczna',
  `liczba_drzwi` INT NOT NULL DEFAULT 5,
  `rok_produkcji` INT NOT NULL DEFAULT 2023,
  `pojemnosc_bagaznika` INT NOT NULL DEFAULT 400
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `historia_pojazdow` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `oryginalne_id_auta` INT NULL,
  `marka` VARCHAR(50) NOT NULL,
  `model` VARCHAR(50) NOT NULL,
  `cena_za_dobe_wtedy` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `historia_uzytkownikow` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `oryginalne_id_uzytkownika` INT NULL,
  `imie` VARCHAR(50) NOT NULL,
  `nazwisko` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wypozyczenia` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_uzytkownika` INT NULL,
  `id_historii_uzytkownika` INT NULL,
  `id_historii_samochodu` INT NOT NULL,
  `pakiet_ochrony` ENUM('podstawowy', 'premium', 'vip') DEFAULT 'podstawowy',
  `data_start` DATE NOT NULL,
  `data_end` DATE NOT NULL,
  `status` ENUM('oczekuje', 'zatwierdzone', 'zakonczone', 'anulowane') DEFAULT 'oczekuje',
  `koszt_calkowity` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownicy`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_historii_uzytkownika`) REFERENCES `historia_uzytkownikow`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_historii_samochodu`) REFERENCES `historia_pojazdow`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `opinie` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `autor` VARCHAR(100) NOT NULL,
  `samochod` VARCHAR(100) NOT NULL,
  `tresc` TEXT NOT NULL,
  `ocena` INT NOT NULL DEFAULT 5,
  `data_dodania` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `samochody` (`marka`, `model`, `kategoria`, `cena_za_dobe`, `lokalizacja`, `zdjecie`, `dostepny`, `paliwo`, `moc`, `skrzynia`, `liczba_drzwi`, `rok_produkcji`, `pojemnosc_bagaznika`) VALUES
('Volvo', 'S60 Polestar', 'premium', 290.00, 'siedlce', 'volvo-s60.png', 1, 'Benzyna', 315, 'Automatyczna', 4, 2022, 442),
('Audi', 'RS3 Sportback', 'premium', 350.00, 'warszawa', 'audi-rs3.png', 1, 'Benzyna', 400, 'Automatyczna', 5, 2023, 335),
('Porsche', 'Macan GTS', 'suv', 420.00, 'sokolow', 'macan-gts.png', 1, 'Benzyna', 440, 'Automatyczna', 5, 2022, 458),
('BMW', 'X5 M50i', 'suv', 490.00, 'warszawa', 'bmw-x5.png', 1, 'Benzyna', 530, 'Automatyczna', 5, 2023, 650),
('Toyota', 'Yaris Hybrid', 'ekonomiczne', 120.00, 'siedlce', 'toyota-yaris.png', 1, 'Hybryda', 116, 'Automatyczna', 5, 2024, 286),
('Volkswagen', 'Golf GTI', 'ekonomiczne', 190.00, 'sokolow', 'volkswagen-golf.png', 1, 'Benzyna', 245, 'Manualna', 5, 2022, 374),
('Tesla', 'Model 3 Performance', 'elektryczne', 310.00, 'warszawa', 'tesla-3.png', 1, 'Elektryczny', 513, 'Automatyczna', 4, 2023, 542),
('Kia', 'EV6 GT', 'elektryczne', 280.00, 'siedlce', 'kia-ev6.png', 1, 'Elektryczny', 585, 'Automatyczna', 5, 2023, 490);


INSERT INTO `opinie` (`autor`, `samochod`, `tresc`, `ocena`) VALUES
('Michał K.', 'Volvo S60', 'Wynajęcie Volvo S60 na weekend to był strzał w dziesiątkę. Auto czyste, pachnące, w stanie salonowym. Sam proces odbioru przez aplikację zajął mi dosłownie chwilę. Na pewno wrócę!', 5),
('Karolina W.', 'Porsche Macan', 'Profesjonalne podejście do klienta i zero ukrytych kosztów. Ubezpieczenie w cenie daje ogromny komfort psychiczny. Porsche Macan sprawdziło się idealnie w trasie w góry.', 5),
('Tomasz R.', 'Audi RS3', 'Świetna wypożyczalnia. Potrzebowałem szybkiego auta miejskiego na jeden dzień - formalności ograniczone do minimum, świetna cena i bezproblemowy zwrot.', 5),
('Janusz B.', 'Toyota Yaris', 'Auto idealne do miasta, bardzo mało pali, hybryda robi swoje. Obsługa klienta MotoRent na najwyższym poziomie, wszystko jasno wyjaśnione.', 4),
('Kamil G.', 'Tesla Model 3', 'Mój pierwszy raz w aucie elektrycznym i jestem zachwycony! Przyspieszenie urywa głowę, a system MotoRent pozwolił mi na rezerwację bez zbędnego czekania.', 5);