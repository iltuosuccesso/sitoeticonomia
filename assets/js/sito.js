/*
  Eticonomia — comportamenti del sito.
  Ripresi dal template Xiomi solo quelli usati: menu per telefono, pannello
  laterale, testata fissa, video in finestra, torna su. Le animazioni
  d'ingresso (WOW) sono state tolte: sui telefoni tenevano il testo nascosto.
  In più l'invio del modulo di contatto senza ricaricare la pagina.
*/
(function ($) {
  "use strict";

  $(function () {
    // Menu per telefono: copia la navigazione nel pannello laterale.
    $("#mobile-menu").meanmenu({
      meanMenuContainer: ".mobile-menu",
      meanScreenWidth: "1199",
      meanExpand: ['<i class="far fa-plus"></i>'],
    });

    // Pannello laterale: si apre con l'hamburger, si chiude con la X o cliccando fuori.
    $(".sidebar__toggle").on("click", function () {
      $(".offcanvas__info").addClass("info-open");
      $(".offcanvas__overlay").addClass("overlay-open");
    });
    $(".offcanvas__close, .offcanvas__overlay").on("click", function () {
      $(".offcanvas__info").removeClass("info-open");
      $(".offcanvas__overlay").removeClass("overlay-open");
    });

    // Testata fissa dopo 250 px e pulsante «torna su» dopo 20 px.
    $(window).on("scroll", function () {
      var y = $(this).scrollTop();
      $("#header-sticky").toggleClass("sticky", y > 250);
      $("#back-top").toggleClass("show", y > 20);
    });
    $("#back-top").on("click", function () {
      $("html, body").animate({ scrollTop: 0 }, 800);
      return false;
    });

    // Video: l'indirizzo di incorporamento (YouTube senza cookie o Facebook)
    // è già nel link e si carica solo quando il visitatore clicca.
    $(".video-popup").magnificPopup({ type: "iframe" });

    // Modulo di contatto: invia a contatti.php e mostra l'esito sotto il pulsante.
    $(".modulo form").on("submit", function (e) {
      e.preventDefault();
      var $form = $(this);
      var $esito = $form.find(".esito");
      var $bottone = $form.find("button[type=submit]");
      $bottone.prop("disabled", true);
      $esito.removeClass("ok errore").text("Invio in corso…");
      $.ajax({ type: "POST", url: $form.attr("action"), data: $form.serialize(), dataType: "json" })
        .done(function (r) {
          $esito.addClass("ok").text(r.messaggio);
          $form[0].reset();
        })
        .fail(function (x) {
          var r = x.responseJSON;
          $esito.addClass("errore").text(r && r.messaggio ? r.messaggio : "Non è stato possibile inviare il messaggio. Riprova o scrivi a info@eticonomia.com.");
        })
        .always(function () {
          $bottone.prop("disabled", false);
        });
    });
  });
})(jQuery);
