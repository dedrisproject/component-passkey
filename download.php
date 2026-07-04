<?php
/**
 * Builds and streams the integration snippet as a zip:
 * the WebAuthn library, the API endpoints, the client JS and the guide.
 */

declare(strict_types=1);

$files = [
    'passkey-snippet/lib/WebAuthnHelper.php' => __DIR__ . '/lib/WebAuthnHelper.php',
    'passkey-snippet/lib/CredentialStore.php' => __DIR__ . '/lib/CredentialStore.php',
    'passkey-snippet/api.php'                => __DIR__ . '/api.php',
    'passkey-snippet/config.php'             => __DIR__ . '/config.php',
    'passkey-snippet/lang/it.php'            => __DIR__ . '/lang/it.php',
    'passkey-snippet/lang/en.php'            => __DIR__ . '/lang/en.php',
    'passkey-snippet/assets/app.js'          => __DIR__ . '/assets/app.js',
    'passkey-snippet/INTEGRATION.md'         => __DIR__ . '/INTEGRATION.md',
];

$tmp = tempnam(sys_get_temp_dir(), 'passkey-snippet');
$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    exit('Could not create zip archive');
}
foreach ($files as $zipPath => $realPath) {
    $zip->addFile($realPath, $zipPath);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="passkey-snippet.zip"');
header('Content-Length: ' . filesize($tmp));
readfile($tmp);
unlink($tmp);
