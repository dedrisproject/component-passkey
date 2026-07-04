<?php
return [
    // generic
    'app.title'            => 'Passkey Component — PHP passkey login demo',
    'app.subtitle'         => 'Open source component to add passkey (WebAuthn) login to any PHP website, with zero dependencies.',
    'lang.switch'          => 'Italiano',
    'lang.switch.code'     => 'it',

    // logged-in section
    'session.title'        => 'Your session',
    'session.logged_as'    => 'You are logged in as <strong>{user}</strong>',
    'session.method.default'  => 'demo auto-login',
    'session.method.form'     => 'form login',
    'session.method.passkey'  => 'passkey login',
    'session.hint'         => 'To create a passkey you must already be authenticated: a passkey always attaches to an existing account, it does not replace sign-up. In this demo you are logged in by default, so you can create one right away and use it for future logins.',
    'btn.create_passkey'   => 'Create Passkey',
    'btn.logout'           => 'Logout',
    'passkeys.registered'  => 'Registered passkeys',
    'passkeys.none'        => 'No passkeys registered yet.',
    'passkeys.delete'      => 'Delete',
    'passkeys.created'     => 'created on',

    // login section
    'login.title'          => 'Sign in',
    'login.username'       => 'Username',
    'login.password'       => 'Password',
    'btn.login_form'       => 'Sign in with form',
    'btn.login_passkey'    => 'Sign in with passkey',
    'login.or'             => 'or',
    'login.demo_hint'      => 'Demo credentials: <code>demo</code> / <code>demo</code>',

    // how-it-works section
    'howit.title'      => 'How passkeys work',
    'howit.q1.title'   => 'Why do I need to be logged in to create one?',
    'howit.q1.text'    => 'A passkey is an <strong>additional</strong> login method that attaches to an existing account. The typical flow is: the user signs up (or signs in) with username and password, and while logged in adds a passkey from their profile. From then on they can sign in without a password.',
    'howit.q2.title'   => 'Where does the key live?',
    'howit.q2.text'    => 'The private key is created and kept <strong>on your device</strong> — in your notebook\'s or smartphone\'s secure chip, protected by fingerprint/face/PIN — or in your password manager (iCloud Keychain, Google Password Manager, 1Password…). It never leaves the device: the website receives and stores <strong>only the public key</strong>, which is not a secret. That is why there is nothing to steal server-side and phishing does not work: the passkey only signs for the domain it was created on.',
    'howit.q3.title'   => 'What if I want to sign in from my smartphone?',
    'howit.q3.text'    => 'If the passkey created on your notebook is synced to the cloud (iCloud, Google…), it automatically shows up on your other devices in the same ecosystem. If it is stored only on the notebook, that key is <strong>not there</strong> on your smartphone and you cannot use it: sign in with your password and register a <strong>second passkey</strong> from the phone (an account can have as many as you like, one per device), or use the "use another device" QR-code option your browser offers.',
    'howit.q4.title'   => 'Can I drop the password entirely?',
    'howit.q4.text'    => 'Yes: once at least one passkey is registered, you can disable username/password login for that account and enable <strong>passkey-only access</strong> — no more weak, reused or stolen passwords. In your integration this is just a flag on the account that hides the classic login form. Tip: before disabling the password, make sure the user has at least two passkeys (or another recovery path).',

    // download section
    'download.title'       => 'Download the snippet',
    'download.text'        => 'Download the source ready to be integrated into your PHP website: dependency-free WebAuthn library, API endpoints, client JavaScript and integration instructions.',
    'btn.download'         => 'Download snippet (.zip)',

    // debug console
    'debug.title'          => 'Debug console',
    'debug.clear'          => 'Clear',
    'debug.ready'          => 'Ready. WebAuthn operations will be traced here.',

    // js messages
    'js.not_supported'         => 'This browser does not support WebAuthn/passkeys.',
    'js.reg.start'             => 'Passkey registration: requesting options from the server…',
    'js.reg.options'           => 'Options received (challenge, rp, user). Opening the OS dialog…',
    'js.reg.created'           => 'Credential created by the authenticator. Sending to the server for verification…',
    'js.reg.done'              => 'Passkey registered successfully! ✔',
    'js.reg.cancel'            => 'Registration cancelled or not allowed.',
    'js.auth.start'            => 'Passkey login: requesting a challenge from the server…',
    'js.auth.options'          => 'Challenge received. Opening the OS dialog…',
    'js.auth.asserted'         => 'Assertion signed by the authenticator. Sending to the server for verification…',
    'js.auth.done'             => 'Signature verified, you are in! ✔ Reloading page…',
    'js.auth.cancel'           => 'Passkey login cancelled or failed.',
    'js.form.start'            => 'Sending form credentials to the server…',
    'js.form.done'             => 'Form login successful. Reloading page…',
    'js.form.fail'             => 'Invalid credentials.',
    'js.logout.done'           => 'Logged out. Reloading page…',
    'js.delete.confirm'        => 'Delete this passkey?',
    'js.delete.done'           => 'Passkey deleted.',
    'js.server_error'          => 'Server error',
];
