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
    'session.hint'         => 'In this demo you are logged in by default: create a passkey now and use it for future logins.',
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
