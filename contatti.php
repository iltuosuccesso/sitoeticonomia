<?php
/**
 * Riceve i moduli di contatto del sito (contatti.html e richiesta-faber.html)
 * e li inoltra per email a info@eticonomia.com.
 *
 * Riceve (POST): nome, email, oggetto, messaggio, consenso; facoltativi
 * organizzazione, telefono, argomento; sito_web è una trappola per i robot
 * e deve restare vuoto.
 * Restituisce JSON {"messaggio": "..."} con codice 200 se l'email è partita,
 * 422 se i dati non vanno bene, 429 se lo stesso indirizzo IP ha scritto
 * troppe volte nell'ultima ora, 500 se il server non è riuscito a inviare.
 */

const DESTINATARIO = 'info@eticonomia.com';
const MITTENTE = 'sito@eticonomia.com';
const INVII_ALL_ORA = 5;

header('Content-Type: application/json; charset=utf-8');

/**
 * Chiude la richiesta con un codice HTTP e un messaggio per il visitatore.
 */
function rispondi(int $codice, string $messaggio): void
{
    http_response_code($codice);
    echo json_encode(['messaggio' => $messaggio], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Legge un campo del modulo: toglie spazi, tag e a capo (questi ultimi
 * solo se $una_riga), e lo taglia a $massimo caratteri.
 */
function campo(string $nome, int $massimo, bool $una_riga = true): string
{
    $valore = trim(strip_tags((string) ($_POST[$nome] ?? '')));
    if ($una_riga) {
        $valore = preg_replace('/[\r\n\t]+/', ' ', $valore);
    }
    return mb_substr($valore, 0, $massimo);
}

/**
 * Conta gli invii dello stesso IP nell'ultima ora in un file temporaneo;
 * restituisce false quando il limite è superato.
 */
function entro_il_limite(string $ip): bool
{
    $file = sys_get_temp_dir() . '/eticonomia-contatti-' . hash('sha256', $ip);
    $adesso = time();
    $invii = is_file($file) ? array_filter(
        array_map('intval', explode("\n", (string) file_get_contents($file))),
        fn ($t) => $t > $adesso - 3600
    ) : [];
    if (count($invii) >= INVII_ALL_ORA) {
        return false;
    }
    $invii[] = $adesso;
    file_put_contents($file, implode("\n", $invii), LOCK_EX);
    return true;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    rispondi(405, 'Il modulo va inviato dalla pagina Contatti.');
}

// Un robot che compila la trappola riceve un finto «inviato» e nessuna email parte.
if (campo('sito_web', 200) !== '') {
    rispondi(200, 'Grazie, il messaggio è stato inviato.');
}

$nome = campo('nome', 120);
$email = filter_var(campo('email', 160), FILTER_VALIDATE_EMAIL);
$organizzazione = campo('organizzazione', 160);
$telefono = campo('telefono', 40);
$oggetto = campo('oggetto', 160);
$argomento = campo('argomento', 60) ?: 'Contatto dal sito';
$messaggio = campo('messaggio', 5000, false);

if ($nome === '' || !$email || $oggetto === '' || $messaggio === '') {
    rispondi(422, 'Compila nome, email, oggetto e messaggio.');
}
if (($_POST['consenso'] ?? '') !== 'si') {
    rispondi(422, 'Per inviare il messaggio serve la presa visione dell\'informativa privacy.');
}
if (!entro_il_limite($_SERVER['REMOTE_ADDR'] ?? '')) {
    rispondi(429, 'Hai già inviato diversi messaggi: riprova più tardi o scrivi a ' . DESTINATARIO . '.');
}

$corpo = "Nuovo messaggio dal sito eticonomia.com\n"
    . "Argomento: $argomento\n\n"
    . "Nome: $nome\n"
    . "Email: $email\n"
    . ($telefono !== '' ? "Telefono: $telefono\n" : '')
    . ($organizzazione !== '' ? "Organizzazione: $organizzazione\n" : '')
    . "Oggetto: $oggetto\n\n"
    . $messaggio . "\n\n"
    . '— Inviato il ' . date('d/m/Y H:i') . ' con consenso all\'informativa privacy.';

$intestazioni = [
    'From' => 'Sito Eticonomia <' . MITTENTE . '>',
    'Reply-To' => $email,
    'Content-Type' => 'text/plain; charset=UTF-8',
];
$titolo = '=?UTF-8?B?' . base64_encode("[$argomento] $oggetto") . '?=';

if (!mail(DESTINATARIO, $titolo, $corpo, $intestazioni, '-f' . MITTENTE)) {
    rispondi(500, 'Non è stato possibile inviare il messaggio. Scrivi direttamente a ' . DESTINATARIO . '.');
}

rispondi(200, 'Grazie, il messaggio è stato inviato. Ti risponderemo al più presto.');
