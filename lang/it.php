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
    'session.hint'         => 'In questa demo sei loggato di default: crea una passkey ora e usala per i futuri accessi.',
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
