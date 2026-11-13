# eticonomia.com

Sito pubblico di **Eticonomia S.R.L.** (P.IVA 12497251004, Via Santa Maria del Colle 1, 02038 Scandriglia RI).

Dal novembre 2026 è il sito culturale del progetto (testi del documento «SITO ETICONOMIA 2»):
otto pagine — Home, La visione, Principi e modello, La storia, Formazione, Dalla visione alla
pratica, Incontri e contributi, Contatti — più la richiesta informazioni sul percorso Faber e
l'informativa privacy e cookie. La parte operativa (adesione, circuito, Faber) rimanda a Talentya.
Fino a ottobre era una copia di evobrain.club: le vecchie pagine sono reindirizzate in `.htaccess`.

## Come è fatto

- Grafica: template **Xiomi** (Pixel-plus, ThemeForest) — `assets/css/main.css`, Bootstrap,
  Font Awesome, meanmenu, magnific-popup — con sopra `assets/css/sito.css` (colori del marchio,
  font in locale, blocchi propri). In `main.css` è stato tolto solo l'`@import` di Google Fonts.
- **Le pagine si modificano in `pagine/`**, non nella cartella principale: ogni file contiene
  solo il proprio contenuto; testata e piede comuni sono `pagine/_testata.html` e
  `pagine/_piede.html`. Dopo ogni modifica:

  ```
  python3 strumenti/costruisci.py
  ```

  e si mettono in git sia le sorgenti sia le pagine generate (sul server non si costruisce niente).
- Modulo di contatto: `contatti.php` invia a info@eticonomia.com con `mail()` del server
  (trappola per robot, limite di 5 invii all'ora per IP).
- Nessun cookie proprio e nessun servizio esterno all'apertura: font e anteprime dei video sono
  in locale, YouTube (senza cookie) e Facebook si caricano solo quando si avvia il video.

## Pubblicazione

Plesk, dominio eticonomia.com, Git «sitoeticonomia» dal ramo `main` in `/httpdocs`.

## Da completare

- Articoli e approfondimenti, Radici etiche, Rassegna stampa: oggi «in preparazione».
- Pagina dedicata ai cofondatori su Talentya: oggi il link porta a talentya.it/#chi-siamo.
- Informativa privacy e cookie: testo da far rivedere a Eticonomia.
