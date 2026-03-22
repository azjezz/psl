<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\SMTP\Client\TransportConfiguration;
use Psl\SMTP\Security;
use Psl\TCP;
use Psl\TLS;

// All transport settings are immutable with fluent with*() builders
$config = TransportConfiguration::default()
    ->withHost('smtp.example.com')
    ->withPort(465)
    ->withSecurity(Security::TLS)
    ->withLocalHostname('client.example.com')
    ->withPipelining(true)
    ->withChunking(true)
    ->withChunkSize(32_768)
    ->withAllowPartialSuccess(false);

// TCP and TLS settings are configurable independently
$config = $config
    ->withConnectConfiguration(new TCP\ConnectConfiguration(noDelay: true))
    ->withTlsClientConfiguration(new TLS\ClientConfiguration(peerVerification: true, allowSelfSigned: false));
