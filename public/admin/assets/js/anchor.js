$(document).ready(function () {
    $('.app-sidebar-menu a[href^="#"]').on('click', function (e) {
        e.preventDefault();

        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 600); // Durée de l'animation en millisecondes (600 ms = 0.6 secondes)
        }
    });

    // Ajouter la classe "active" au lien du menu correspondant à la section visible
    $(window).on('scroll', function () {
        var scrollPosition = $(window).scrollTop(); // Position actuelle du scroll
        var windowHeight = $(window).height(); // Hauteur de la fenêtre

        $('.app-sidebar-menu a[href^="#"]').each(function () {
            var target = $($(this).attr('href')); // Section cible
            if (target.length) {
                var targetTop = target.offset().top;
                var targetBottom = targetTop + target.outerHeight();

                // Vérifie si la section est dans la zone visible
                if (scrollPosition + (windowHeight / 2) >= targetTop && scrollPosition + (windowHeight / 2) <= targetBottom) {
                    $('.app-sidebar-menu a[href^="#"]').removeClass('active'); // Retire la classe "active" de tous les liens
                    $(this).addClass('active'); // Ajoute la classe "active" au lien correspondant
                }
            }
        });
    });
});
