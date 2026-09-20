<?php
// Basit dağıtım webhook'u - GitHub Actions tarafından push sonrası çağrılır.
// Gizli anahtarı doğrular, gönderilen zip'i bu dosyanın bulunduğu klasöre (public_html) açar.

$secret = '7e2c98e80251fe6610caf9fd08de4ef8cf6088ef';

header('Content-Type: text/plain; charset=utf-8');

$gelenSecret = $_SERVER['HTTP_X_DEPLOY_SECRET'] ?? '';
if (!hash_equals($secret, $gelenSecret)) {
    http_response_code(403);
    echo "Yetkisiz istek";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['payload']['tmp_name'])) {
    http_response_code(400);
    echo "payload dosyasi bekleniyor";
    exit;
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    echo "ZipArchive PHP uzantisi bu sunucuda yok";
    exit;
}

$zip = new ZipArchive();
if ($zip->open($_FILES['payload']['tmp_name']) !== true) {
    http_response_code(500);
    echo "Zip acilamadi";
    exit;
}

$hedefKlasor = __DIR__;
$basarili = $zip->extractTo($hedefKlasor);
$zip->close();

if (!$basarili) {
    http_response_code(500);
    echo "Zip acma/kopyalama basarisiz";
    exit;
}

echo "OK: " . date('Y-m-d H:i:s');
