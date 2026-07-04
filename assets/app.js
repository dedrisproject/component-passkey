/**
 * Client-side passkey logic + debug console.
 * Expects window.PASSKEY_I18N (translated strings) injected by index.php.
 */
(function () {
  'use strict';

  const I18N = window.PASSKEY_I18N || {};
  const t = (key) => I18N[key] || key;

  // ---------- debug console ----------

  const consoleEl = document.getElementById('debug-console');

  function log(message, kind = 'info') {
    if (!consoleEl) return;
    const line = document.createElement('div');
    line.className = 'dbg-line dbg-' + kind;
    const time = new Date().toLocaleTimeString();
    line.innerHTML = '<span class="dbg-time">' + time + '</span> ';
    line.appendChild(document.createTextNode(message));
    consoleEl.appendChild(line);
    consoleEl.scrollTop = consoleEl.scrollHeight;
  }

  function logServerDebug(payload) {
    (payload && payload.debug ? payload.debug : []).forEach((msg) => log('[server] ' + msg, 'server'));
  }

  document.getElementById('debug-clear')?.addEventListener('click', () => {
    consoleEl.innerHTML = '';
    log(t('debug.ready'));
  });

  log(t('debug.ready'));
  if (!window.PublicKeyCredential) {
    log(t('js.not_supported'), 'error');
  }

  // ---------- helpers ----------

  const b64uToBuf = (b64u) =>
    Uint8Array.from(atob(b64u.replace(/-/g, '+').replace(/_/g, '/')), (c) => c.charCodeAt(0));

  const bufToB64u = (buf) =>
    btoa(String.fromCharCode(...new Uint8Array(buf)))
      .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

  async function api(action, body) {
    const res = await fetch('api.php?action=' + action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body || {}),
    });
    const payload = await res.json().catch(() => ({}));
    logServerDebug(payload);
    if (!payload.ok) {
      throw new Error(payload.error || t('js.server_error'));
    }
    return payload;
  }

  const reload = () => setTimeout(() => window.location.reload(), 900);

  // ---------- create passkey ----------

  document.getElementById('btn-create-passkey')?.addEventListener('click', async () => {
    try {
      log(t('js.reg.start'));
      const { publicKey } = await api('register-options');
      log(t('js.reg.options'));

      publicKey.challenge = b64uToBuf(publicKey.challenge);
      publicKey.user.id = b64uToBuf(publicKey.user.id);
      publicKey.excludeCredentials = (publicKey.excludeCredentials || []).map((c) => ({
        ...c, id: b64uToBuf(c.id),
      }));

      const credential = await navigator.credentials.create({ publicKey });
      log(t('js.reg.created'));

      await api('register-verify', {
        clientDataJSON: bufToB64u(credential.response.clientDataJSON),
        attestationObject: bufToB64u(credential.response.attestationObject),
        label: navigator.userAgentData?.platform || navigator.platform || '',
      });
      log(t('js.reg.done'), 'ok');
      reload();
    } catch (err) {
      if (err.name === 'NotAllowedError' || err.name === 'AbortError') {
        log(t('js.reg.cancel'), 'error');
      } else {
        log('✘ ' + err.message, 'error');
      }
    }
  });

  // ---------- login with passkey ----------

  document.getElementById('btn-login-passkey')?.addEventListener('click', async () => {
    try {
      log(t('js.auth.start'));
      const { publicKey } = await api('login-options');
      log(t('js.auth.options'));

      publicKey.challenge = b64uToBuf(publicKey.challenge);
      publicKey.allowCredentials = (publicKey.allowCredentials || []).map((c) => ({
        ...c, id: b64uToBuf(c.id),
      }));

      const assertion = await navigator.credentials.get({ publicKey });
      log(t('js.auth.asserted'));

      await api('login-verify', {
        credentialId: bufToB64u(assertion.rawId),
        clientDataJSON: bufToB64u(assertion.response.clientDataJSON),
        authenticatorData: bufToB64u(assertion.response.authenticatorData),
        signature: bufToB64u(assertion.response.signature),
        userHandle: assertion.response.userHandle ? bufToB64u(assertion.response.userHandle) : null,
      });
      log(t('js.auth.done'), 'ok');
      reload();
    } catch (err) {
      if (err.name === 'NotAllowedError' || err.name === 'AbortError') {
        log(t('js.auth.cancel'), 'error');
      } else {
        log('✘ ' + err.message, 'error');
      }
    }
  });

  // ---------- form login ----------

  document.getElementById('login-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      log(t('js.form.start'));
      await api('form-login', {
        username: document.getElementById('login-username').value,
        password: document.getElementById('login-password').value,
      });
      log(t('js.form.done'), 'ok');
      reload();
    } catch (err) {
      log('✘ ' + (err.message || t('js.form.fail')), 'error');
    }
  });

  // ---------- logout ----------

  document.getElementById('btn-logout')?.addEventListener('click', async () => {
    try {
      await api('logout');
      log(t('js.logout.done'), 'ok');
      reload();
    } catch (err) {
      log('✘ ' + err.message, 'error');
    }
  });

  // ---------- delete passkey ----------

  document.querySelectorAll('[data-delete-passkey]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm(t('js.delete.confirm'))) return;
      try {
        await api('delete-passkey', { credentialId: btn.dataset.deletePasskey });
        log(t('js.delete.done'), 'ok');
        reload();
      } catch (err) {
        log('✘ ' + err.message, 'error');
      }
    });
  });
})();
