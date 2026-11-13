#!/usr/bin/env python3
"""
Costruisce le pagine del sito eticonomia.com.

Ogni pagina vive in pagine/<nome>.html e contiene solo il proprio contenuto,
preceduto da un'intestazione con i dati della pagina:

    <!--
    titolo: La visione
    descrizione: Testo per i motori di ricerca.
    colore: #F07A10
    -->

Lo script aggiunge testata, menu, piede e script comuni (pagine/_testata.html
e pagine/_piede.html) e scrive il risultato nella cartella principale, da
dove Plesk pubblica il sito. Le pagine generate vanno messe in git insieme
alle sorgenti: sul server non si costruisce niente.

Uso:  python3 strumenti/costruisci.py
"""

import re
import sys
from pathlib import Path

RADICE = Path(__file__).resolve().parent.parent
SORGENTI = RADICE / "pagine"
DOMINIO = "https://eticonomia.com"

# Voci del menu principale: (file, etichetta). La Home è il logo.
MENU = [
    ("visione.html", "La visione"),
    ("principi.html", "Principi e modello"),
    ("storia.html", "La storia"),
    ("formazione.html", "Formazione"),
    ("pratica.html", "Dalla visione alla pratica"),
    ("incontri.html", "Incontri e contributi"),
    ("contatti.html", "Contatti"),
]

# Ultima voce del menu: l'area membri è la piattaforma Eticonomia v2.
ACCEDI = "https://app.eticonomia.com/"

INTESTAZIONE = re.compile(r"\A\s*<!--(.*?)-->\s*", re.S)


def leggi_pagina(percorso: Path) -> tuple[dict, str]:
    """Legge una sorgente: restituisce (dati dell'intestazione, contenuto)."""
    testo = percorso.read_text(encoding="utf-8")
    trovato = INTESTAZIONE.match(testo)
    if not trovato:
        sys.exit(f"{percorso.name}: manca l'intestazione <!-- titolo: ... -->")
    dati = {}
    for riga in trovato.group(1).strip().splitlines():
        chiave, _, valore = riga.partition(":")
        dati[chiave.strip()] = valore.strip()
    for obbligatoria in ("titolo", "descrizione"):
        if not dati.get(obbligatoria):
            sys.exit(f"{percorso.name}: manca «{obbligatoria}» nell'intestazione")
    return dati, testo[trovato.end():]


def menu_html(attiva: str) -> str:
    """Restituisce le voci <li> del menu, con «active» sulla pagina aperta, più «Accedi»."""
    voci = []
    for file, etichetta in MENU:
        classe = ' class="active"' if file == attiva else ""
        voci.append(f'<li{classe}><a href="{file}">{etichetta}</a></li>')
    voci.append(f'<li class="accedi"><a href="{ACCEDI}"><i class="fa-regular fa-user"></i> Accedi</a></li>')
    return "\n                                    ".join(voci)


def componi(nome: str, dati: dict, contenuto: str, testata: str, piede: str) -> str:
    """Unisce testata, contenuto e piede e riempie i segnaposto {{...}}."""
    titolo = dati["titolo"]
    titolo_completo = "Eticonomia — L'agire economico al servizio dell'uomo" if nome == "index.html" else f"{titolo} — Eticonomia"
    indirizzo = f"{DOMINIO}/" if nome == "index.html" else f"{DOMINIO}/{nome}"
    valori = {
        "titolo": titolo_completo,
        "descrizione": dati["descrizione"],
        "indirizzo": indirizzo,
        "colore": dati.get("colore", "#dccaa6"),
        "menu": menu_html(nome),
    }
    pagina = testata + contenuto.rstrip() + "\n" + piede
    for chiave, valore in valori.items():
        pagina = pagina.replace("{{" + chiave + "}}", valore)
    rimasti = re.findall(r"\{\{\w+\}\}", pagina)
    if rimasti:
        sys.exit(f"{nome}: segnaposto senza valore {sorted(set(rimasti))}")
    return pagina


def main() -> None:
    """Costruisce tutte le pagine di pagine/ che non iniziano con «_»."""
    testata = (SORGENTI / "_testata.html").read_text(encoding="utf-8")
    piede = (SORGENTI / "_piede.html").read_text(encoding="utf-8")
    for sorgente in sorted(SORGENTI.glob("*.html")):
        if sorgente.name.startswith("_"):
            continue
        dati, contenuto = leggi_pagina(sorgente)
        (RADICE / sorgente.name).write_text(componi(sorgente.name, dati, contenuto, testata, piede), encoding="utf-8")
        print(f"scritta {sorgente.name}")


if __name__ == "__main__":
    main()
