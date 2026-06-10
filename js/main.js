$(document).ready(function () {

    $(window).scroll(function () {
        var scrollTop = $(this).scrollTop();
        $('.nav-bar').toggleClass('scrolled', scrollTop > 650);
        $('#backToTop').toggleClass('visible', scrollTop > 600);
    });

    $('#backToTop').on('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    $('.faq-question').on('click', function () {
        var $item = $(this).closest('.faq-item');
        $('.faq-item').not($item).removeClass('active').find('.faq-answer').slideUp(200);
        $item.toggleClass('active');
        $item.find('.faq-answer').slideToggle(200);
    });


    $('.profile-trigger').on('click', function (e) {
        e.stopPropagation();
        $('#profileDropdown').toggleClass('active');
    });
    $(document).on('click', function () {
        $('#profileDropdown').removeClass('active');
    });


    if ($('#registerForm').length > 0) {
        $('#regEmail').on('blur', function () {
            var email = $(this).val().trim();
            var $input = $(this);
            var $err = $('#error-email');

            if (email.length === 0) return;

            $.post('sprawdz_email.php', { email: email }, function (odpowiedz) {
                if (odpowiedz === 'zajety') {
                    $input.addClass('field-input-error');
                    $err.text('Ten adres e-mail jest już zajęty!');
                } else {
                    $input.removeClass('field-input-error');
                    $err.text('');
                }
            });
        });

        $('#registerForm').on('submit', function (e) {
            var imie = $('#regImie').val().trim();
            var nazwisko = $('#regNazwisko').val().trim();
            var dataUr = $('#regDataUr').val();
            var password = $('#regPassword').val();
            var jestBlad = false;

            $('#error-imie, #error-nazwisko, #error-data-ur, #error-haslo').text('');
            $('#regImie, #regNazwisko, #regDataUr, #regPassword').removeClass('field-input-error');

            var nameReg = /^[a-zA-ZąęćłńóśźżĄĘĆŁŃÓŚŹŻ]{3,}$/;

            if (!nameReg.test(imie)) {
                jestBlad = true;
                $('#regImie').addClass('field-input-error');
                $('#error-imie').text('Wymagane minimum 3 litery.');
            }

            if (!nameReg.test(nazwisko)) {
                jestBlad = true;
                $('#regNazwisko').addClass('field-input-error');
                $('#error-nazwisko').text('Wymagane minimum 3 litery.');
            }

            if (!dataUr) {
                jestBlad = true;
                $('#regDataUr').addClass('field-input-error');
                $('#error-data-ur').text('Podaj datę.');
            } else {
                var urodziny = new Date(dataUr);
                var dzis = new Date();
                if (urodziny > dzis) {
                    jestBlad = true;
                    $('#regDataUr').addClass('field-input-error');
                    $('#error-data-ur').text('Data nie może być z przyszłości.');
                } else {
                    var wiek = dzis.getFullYear() - urodziny.getFullYear();
                    var m = dzis.getMonth() - urodziny.getMonth();
                    if (m < 0 || (m === 0 && dzis.getDate() < urodziny.getDate())) {
                        wiek--;
                    }
                    if (wiek < 18) {
                        jestBlad = true;
                        $('#regDataUr').addClass('field-input-error');
                        $('#error-data-ur').text('Wymagane ukończone 18 lat.');
                    }
                }
            }

            if (password.length < 8) {
                jestBlad = true;
                $('#regPassword').addClass('field-input-error');
                $('#error-haslo').text('Wymagane minimum 8 znaków.');
            }

            if ($('#regEmail').hasClass('field-input-error')) {
                jestBlad = true;
            }

            if (jestBlad) {
                e.preventDefault();
                $('.field-input-error').first().focus();
                return false;
            }
        });
    }

    var dzisISO = new Date().toISOString().split('T')[0];

    if ($('#indexStart').length > 0) {
        $('#indexStart, #indexEnd').attr('min', dzisISO);
        $('#indexStart').on('change', function () {
            var start = $(this).val();
            $('#indexEnd').attr('min', start);
            if ($('#indexEnd').val() < start) {
                $('#indexEnd').val('');
            }
        });
    }

    if ($('#fleetStart').length > 0) {
        $('#fleetStart, #fleetEnd').attr('min', dzisISO);
    }

    if ($('#realBookingForm').length > 0) {
        function przeliczCeneIDostepnosc() {
            var idAuta = $('#ajaxCarId').val();
            var start = $('#fleetStart').val();
            var end = $('#fleetEnd').val();

            if (start === '' || end === '') return;

            var dodatki = $('.ajax-addon:checked').map(function () {
                return $(this).val();
            }).get();

            var pakiet = $('.ajax-package:checked').val();

            $.ajax({
                url: 'sprawdz_rezerwacje.php',
                method: 'POST',
                data: {
                    samochod_id: idAuta,
                    data_start: start,
                    data_end: end,
                    dodatki: dodatki,
                    pakiet_ochrony: pakiet
                },
                dataType: 'json',
                success: function (response) {
                    if (response.dostepny) {
                        $('#availabilityAlert').removeClass('visible');
                        $('#submitBookingBtn').prop('disabled', false).css('opacity', '1');
                        $('#dynamicPrice').text(response.laczna_cena);
                        $('#priceLabel').text(' za całe ' + response.dni + ' dni');
                    } else {
                        $('#availabilityAlert').text(response.komunikat).addClass('visible');
                        $('#submitBookingBtn').prop('disabled', true).css('opacity', '0.4');
                        $('#dynamicPrice').text('Niedostępny');
                        $('#priceLabel').text('');
                    }
                }
            });
        }

        $('#fleetStart, #fleetEnd, .ajax-addon, .ajax-package').on('change', przeliczCeneIDostepnosc);

        przeliczCeneIDostepnosc();
    }


    var $alerty = $('.alert-success-banner, .alert-success, .alert-danger');
    if ($alerty.length > 0) {
        if (window.history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.delete('msg');
            url.searchParams.delete('err');
            window.history.replaceState({}, document.title, url.pathname + url.search);
        }

        setTimeout(function () {
            $alerty.slideUp(500, function () { $(this).remove(); });
        }, 4000);
    }

    if ($('#reviewsTrack').length > 0) {
        let track = $('#reviewsTrack');

        function przewinKaruzele() {
            let pierwszaKarta = track.children('.review-card').first();
            let szerokoscPrzesuniecia = pierwszaKarta.outerWidth() + 30;

            track.css({
                'transition': 'transform 0.5s ease-in-out',
                'transform': 'translateX(' + (-szerokoscPrzesuniecia) + 'px)'
            });

            setTimeout(function () {
                track.css('transition', 'none');
                pierwszaKarta.appendTo(track);
                track.css('transform', 'translateX(0)');
            }, 500);
        }
        setInterval(przewinKaruzele, 3000);
    }
});