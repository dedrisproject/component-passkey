<?php
return [
    // generic
    'app.title'            => 'Passkey Component — Demo login con passkey in PHP',
    'app.subtitle'         => 'Componente open source per aggiungere il login con passkey (WebAuthn) a qualsiasi sito PHP, senza dipendenze.',
    'lang.switch'          => 'English',
    'lang.switch.code'     => 'en',

    // logged-in section
    'session.title'        => 'La tua sessione',
    'session.logged_as'    => 'Sei loggato come <strong>{user}</strong>',
    'session.method.default'  => 'accesso automatico demo',
    'session.method.form'     => 'accesso con form',
    'session.method.passkey'  => 'accesso con passkey',
    'session.hint'         => 'Per creare una passkey devi essere già autenticato: la passkey si aggancia sempre a un account esistente, non lo sostituisce alla registrazione. In questa demo sei loggato di default, così puoi crearla subito e usarla per i futuri accessi.',
    'btn.create_passkey'   => 'Crea Passkey',
    'btn.logout'           => 'Logout',
    'passkeys.registered'  => 'Passkey registrate',
    'passkeys.none'        => 'Nessuna passkey registrata finora.',
    'passkeys.delete'      => 'Elimina',
    'passkeys.created'     => 'creata il',

    // login section
    'login.title'          => 'Accedi',
    'login.username'       => 'Nome utente',
    'login.password'       => 'Password',
    'btn.login_form'       => 'Accedi con form',
    'btn.login_passkey'    => 'Accedi con passkey',
    'login.or'             => 'oppure',
    'login.demo_hint'      => 'Credenziali demo: <code>demo</code> / <code>demo</code>',

    // how-it-works section
    'howit.title'      => 'Come funzionano le passkey',
    'howit.q1.title'   => 'Perché devo essere già loggato per crearla?',
    'howit.q1.text'    => 'Una passkey è un metodo di accesso <strong>aggiuntivo</strong> che si aggancia a un account esistente. Il flusso tipico è: l\'utente si registra (o accede) con username e password, e da loggato aggiunge una passkey dal suo profilo. Da quel momento può entrare senza password.',
    'howit.q2.title'   => 'Dove si trova la chiave?',
    'howit.q2.text'    => 'La chiave privata viene creata e custodita <strong>sul tuo dispositivo</strong> — nel chip sicuro del notebook o dello smartphone, protetta da impronta/volto/PIN — oppure nel tuo password manager (iCloud Keychain, Google Password Manager, 1Password…). Non lascia mai il dispositivo: il sito riceve e salva <strong>solo la chiave pubblica</strong>, che non è un segreto. Per questo non c\'è niente da rubare lato server e il phishing non funziona: la passkey firma solo per il dominio su cui è stata creata.',
    'howit.q3.title'   => 'E se voglio accedere dallo smartphone?',
    'howit.q3.text'    => 'Se la passkey creata sul notebook è sincronizzata nel cloud (iCloud, Google…), la ritrovi automaticamente sugli altri tuoi dispositivi dello stesso ecosistema. Se invece è salvata solo sul notebook, dallo smartphone quella chiave <strong>non c\'è</strong> e non puoi usarla: accedi con la password e registra una <strong>seconda passkey</strong> da lì (un account può averne quante ne vuole, una per dispositivo), oppure usa l\'opzione "usa un altro dispositivo" con il QR code che il browser ti propone.',
    'howit.q4.title'   => 'Posso eliminare del tutto la password?',
    'howit.q4.text'    => 'Sì: una volta registrata almeno una passkey, puoi disattivare l\'accesso con username e password per quell\'account e abilitare l\'<strong>accesso solo con passkey</strong> — niente più password deboli, riusate o rubate. Nella tua integrazione basta un flag sull\'account che nasconde il form di login classico. Consiglio: prima di disattivare la password assicurati che l\'utente abbia almeno due passkey (o un\'altra via di recupero).',

    // download section
    'download.title'       => 'Scarica lo snippet',
    'download.text'        => 'Scarica il sorgente pronto da integrare nel tuo sito PHP: libreria WebAuthn senza dipendenze, endpoint API, JavaScript client e istruzioni di integrazione.',
    'btn.download'         => 'Scarica snippet (.zip)',

    // debug console
    'debug.title'          => 'Console di debug',
    'debug.clear'          => 'Pulisci',
    'debug.ready'          => 'Pronto. Le operazioni WebAuthn verranno tracciate qui.',

    // js messages
    'js.not_supported'         => 'Questo browser non supporta WebAuthn/passkey.',
    'js.reg.start'             => 'Registrazione passkey: richiedo le opzioni al server…',
    'js.reg.options'           => 'Opzioni ricevute (challenge, rp, user). Apro il dialogo del sistema operativo…',
    'js.reg.created'           => 'Credenziale creata dall\'authenticator. Invio al server per la verifica…',
    'js.reg.done'              => 'Passkey registrata con successo! ✔',
    'js.reg.cancel'            => 'Registrazione annullata o non consentita.',
    'js.auth.start'            => 'Login con passkey: richiedo la challenge al server…',
    'js.auth.options'          => 'Challenge ricevuta. Apro il dialogo del sistema operativo…',
    'js.auth.asserted'         => 'Assertion firmata dall\'authenticator. Invio al server per la verifica…',
    'js.auth.done'             => 'Firma verificata, accesso effettuato! ✔ Ricarico la pagina…',
    'js.auth.cancel'           => 'Accesso con passkey annullato o non riuscito.',
    'js.form.start'            => 'Invio credenziali del form al server…',
    'js.form.done'             => 'Accesso con form riuscito. Ricarico la pagina…',
    'js.form.fail'             => 'Credenziali non valide.',
    'js.logout.done'           => 'Logout effettuato. Ricarico la pagina…',
    'js.delete.confirm'        => 'Eliminare questa passkey?',
    'js.delete.done'           => 'Passkey eliminata.',
    'js.server_error'          => 'Errore dal server',
];
