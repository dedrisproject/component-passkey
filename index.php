<?php
require_once __DIR__ . '/config.php';

$store = new CredentialStore(CREDENTIAL_FILE);
$user = currentUser();
$passkeys = $user ? $store->forUser($user) : [];

// Only the js.* / debug.* strings are needed client-side.
$jsStrings = array_filter($GLOBALS['T'], fn($k) => str_starts_with($k, 'js.') || str_starts_with($k, 'debug.'), ARRAY_FILTER_USE_KEY);

$e = fn(string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= $e($LANG) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e(t('app.title')) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="topbar">
  <div>
    <h1>🔐 <?= $e(t('app.title')) ?></h1>
    <p class="subtitle"><?= $e(t('app.subtitle')) ?></p>
  </div>
  <a class="lang-switch" href="?lang=<?= $e(t('lang.switch.code')) ?>">🌍 <?= $e(t('lang.switch')) ?></a>
</header>

<div class="layout">
  <main>

    <?php if ($user !== null): ?>
    <!-- ============ Section 1: logged in ============ -->
    <section class="card">
      <h2><?= $e(t('session.title')) ?>
        <span class="badge"><?= $e(t('session.method.' . ($_SESSION['login_method'] ?? 'default'))) ?></span>
      </h2>
      <p><?= t('session.logged_as', ['user' => $e($user)]) ?></p>
      <p class="hint"><?= $e(t('session.hint')) ?></p>
      <div class="btn-row">
        <button id="btn-create-passkey" class="btn btn-primary">➕ <?= $e(t('btn.create_passkey')) ?></button>
        <button id="btn-logout" class="btn">🚪 <?= $e(t('btn.logout')) ?></button>
      </div>
    </section>

    <section class="card">
      <h2><?= $e(t('passkeys.registered')) ?></h2>
      <?php if ($passkeys === []): ?>
        <p><?= $e(t('passkeys.none')) ?></p>
      <?php else: ?>
        <ul class="passkey-list">
          <?php foreach ($passkeys as $id => $pk): ?>
          <li>
            <span>
              🔑 <code><?= $e(substr($id, 0, 20)) ?>…</code>
              <span class="meta">
                <?= $e($pk['label'] ?: 'authenticator') ?> ·
                <?= $e(t('passkeys.created')) ?> <?= $e(date('Y-m-d H:i', strtotime($pk['createdAt']))) ?>
              </span>
            </span>
            <button class="btn-danger-link" data-delete-passkey="<?= $e($id) ?>"><?= $e(t('passkeys.delete')) ?></button>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <?php else: ?>
    <!-- ============ Section 2: login ============ -->
    <section class="card">
      <h2><?= $e(t('login.title')) ?></h2>
      <form class="login" id="login-form">
        <label for="login-username"><?= $e(t('login.username')) ?></label>
        <input id="login-username" name="username" autocomplete="username webauthn" required>
        <label for="login-password"><?= $e(t('login.password')) ?></label>
        <input id="login-password" name="password" type="password" autocomplete="current-password" required>
        <div class="btn-row">
          <button type="submit" class="btn btn-primary">📝 <?= $e(t('btn.login_form')) ?></button>
        </div>
      </form>
      <div class="or-divider"><?= $e(t('login.or')) ?></div>
      <div class="btn-row">
        <button id="btn-login-passkey" class="btn btn-success">🔑 <?= $e(t('btn.login_passkey')) ?></button>
      </div>
      <p class="hint"><?= t('login.demo_hint') ?></p>
    </section>
    <?php endif; ?>

    <!-- ============ How passkeys work ============ -->
    <section class="card">
      <h2>💡 <?= $e(t('howit.title')) ?></h2>
      <div class="faq">
        <?php foreach (['q1', 'q2', 'q3', 'q4'] as $q): ?>
        <details<?= $q === 'q1' ? ' open' : '' ?>>
          <summary><?= $e(t("howit.$q.title")) ?></summary>
          <p><?= t("howit.$q.text") ?></p>
        </details>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ============ Section 3: snippet download ============ -->
    <section class="card">
      <h2><?= $e(t('download.title')) ?></h2>
      <p><?= $e(t('download.text')) ?></p>
      <div class="btn-row">
        <a class="btn btn-primary" href="download.php">⬇️ <?= $e(t('btn.download')) ?></a>
      </div>
    </section>

  </main>

  <!-- ============ Debug console ============ -->
  <aside class="debug-panel">
    <div class="debug-header">
      <h2>🖥️ <?= $e(t('debug.title')) ?></h2>
      <button id="debug-clear"><?= $e(t('debug.clear')) ?></button>
    </div>
    <div id="debug-console"></div>
  </aside>
</div>

<footer>
  Passkey Component — open source ·
  <a href="https://github.com/dedrisproject/component-passkey" target="_blank" rel="noopener">GitHub</a>
</footer>

<script>window.PASSKEY_I18N = <?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<script src="assets/app.js"></script>
</body>
</html>
