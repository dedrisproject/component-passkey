# 🔐 Passkey Component

> 🇬🇧 [Read in English](README.md)

Un componente **PHP open source e senza dipendenze** per aggiungere il **login con passkey (WebAuthn)** a qualsiasi sito web — con una pagina demo dotata di console di debug che mostra esattamente cosa succede dietro le quinte.

## Panoramica della demo

La pagina demo ([index.php](index.php)) ha tre sezioni:

1. **La tua sessione** — sei **loggato di default** come utente demo. Da qui puoi premere **Crea Passkey** per registrare una passkey per i futuri accessi, vedere/eliminare le passkey registrate e fare **Logout**.
2. **Accedi** — dopo il logout trovi il classico **form utente/password** (demo: `demo` / `demo`) e il pulsante **Accedi con passkey**.
3. **Scarica lo snippet** — un click scarica uno zip con tutto il necessario per integrare le passkey nel tuo sito PHP (libreria, API, JS, guida).

Sulla destra una **console di debug** traccia ogni passaggio, sia lato client (eventi browser/WebAuthn) sia lato server (generazione della challenge, controlli su clientData, verifica della firma…). L'interfaccia è bilingue (🇮🇹/🇬🇧) con cambio lingua a un click.

## Avvio rapido

Requisiti: PHP 8.0+ con `ext-openssl` (entrambi standard).

```bash
git clone https://github.com/dedrisproject/component-passkey.git
cd component-passkey
php -S localhost:8000
```

Apri <http://localhost:8000> — fatto. In produzione WebAuthn richiede HTTPS, ma i browser consentono `http://localhost` per lo sviluppo.

## Come funziona

```
Browser                                 Server PHP
───────                                 ──────────
Crea Passkey ────────────────────────▶  api.php?action=register-options
                                        genera una challenge casuale (in sessione)
navigator.credentials.create() ◀──────  restituisce rp, user, challenge
(dialogo OS: Touch ID / Windows Hello)
attestationObject ───────────────────▶  api.php?action=register-verify
                                        controlla challenge + origin + rpId,
                                        estrae la chiave pubblica COSE → PEM,
                                        la salva (data/credentials.json)

Accedi con passkey ──────────────────▶  api.php?action=login-options
navigator.credentials.get() ◀─────────  restituisce una nuova challenge
firma ───────────────────────────────▶  api.php?action=login-verify
                                        verifica la firma con la chiave pubblica
                                        salvata → apre la sessione
```

Tutto è implementato in [lib/WebAuthnHelper.php](lib/WebAuthnHelper.php) (~350 righe): decoder CBOR minimale, conversione chiave COSE→PEM (ES256 + RS256), validazione di clientData/authenticatorData e verifica della firma via OpenSSL. **Nessun pacchetto Composer necessario.**

## Integralo nel tuo sito

Premi **Scarica snippet** nella demo (o leggi [INTEGRATION.md](INTEGRATION.md)) — spiega come:

- collegare gli endpoint API alla *tua* sessione e al tuo backend utenti,
- sostituire lo storage su file JSON con una tabella di database,
- collegare i due pulsanti (`Crea passkey`, `Accedi con passkey`) alle tue pagine.

## Le passkey in breve

- **Per crearla devi essere già loggato.** La passkey si aggancia a un account esistente: l'utente accede (o si registra) nel modo classico e poi aggiunge una passkey dal profilo. Per questo la demo parte con te già loggato.
- **La chiave privata vive sul tuo dispositivo** — nel chip sicuro del notebook/smartphone o nel password manager (iCloud Keychain, Google Password Manager, 1Password…). Non lo lascia mai: il server salva solo la chiave pubblica, quindi non c'è niente da rubare e il phishing non funziona (la chiave firma solo per il suo dominio).
- **Altri dispositivi:** se la passkey è sincronizzata nel cloud la ritrovi sugli altri tuoi dispositivi dello stesso ecosistema. Se è salvata solo sul notebook, dallo smartphone non puoi usarla — accedi con la password e registra una seconda passkey dal telefono (un account, tante passkey), oppure usa il flusso QR code "usa un altro dispositivo" del browser.
- **Account solo-passkey:** una volta registrata almeno una passkey puoi disattivare del tutto l'accesso con username e password per quell'account — nella tua integrazione è solo un flag che nasconde il form classico. Prima assicurati che l'utente abbia una seconda passkey o una via di recupero.

## Note di sicurezza

- Le challenge sono casuali, monouso e validate lato server.
- Origin e hash del `rpId` vengono controllati a ogni operazione.
- L'attestation è `none` (il default delle passkey): viene salvata solo la chiave pubblica.
- Il sign counter viene verificato quando l'authenticator lo fornisce.
- La demo salva le credenziali in `data/credentials.json` — va bene per provare, in produzione usa un DB.

## Risoluzione problemi

- **Passkey creata ma non elencata dopo il reload** → la cartella `data/` non è scrivibile dal web server. Soluzione: `chmod 775 data` (oppure `chown www-data data` a seconda della configurazione). La console di debug mostra l'errore esatto ancora prima che si apra il dialogo dell'OS.
- **Il dialogo dell'OS non si apre** → WebAuthn richiede HTTPS (oppure `http://localhost` in sviluppo) e un browser con supporto passkey.
- La console di debug persiste tra i reload della pagina (sessionStorage) — controllala per prima cosa: ogni passaggio client e server viene tracciato lì.

## Struttura del progetto

```
index.php              pagina demo (3 sezioni + console di debug)
api.php                API JSON (registrazione/login/logout)
download.php           genera lo zip dello snippet di integrazione
config.php             bootstrap demo: sessione, utente demo, lingua
lib/WebAuthnHelper.php core WebAuthn (CBOR, COSE→PEM, verifica)
lib/CredentialStore.php storage credenziali su file JSON
assets/app.js          logica client + console di debug
lang/it.php, en.php    traduzioni
INTEGRATION.md         guida all'integrazione (en/it)
```

## Licenza

MIT — usalo, rompilo, miglioralo. Le PR sono benvenute!
