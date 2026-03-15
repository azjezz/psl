<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TCP;
use Psl\TLS;

// Note: This example requires valid TLS certificate files to run.
// Replace the paths below with actual certificate and key files.
$lazy = TLS\LazyAcceptor::default();
$listener = TCP\listen('0.0.0.0', 8443);

$configs = [
    'api.example.com' => TLS\ServerConfiguration::create(TLS\Certificate::create(
        '/etc/ssl/certs/api.pem',
        '/etc/ssl/private/api.key',
    )),
    'www.example.com' => TLS\ServerConfiguration::create(TLS\Certificate::create(
        '/etc/ssl/certs/www.pem',
        '/etc/ssl/private/www.key',
    )),
];

$default = TLS\ServerConfiguration::create(TLS\Certificate::create(
    '/etc/ssl/certs/default.pem',
    '/etc/ssl/private/default.key',
));

while (true) {
    $stream = $listener->accept();
    $hello = $lazy->accept($stream);
    $config = $configs[$hello->getServerName()] ?? $default;
    $tls = $hello->complete($config);
    // ... handle connection
}
