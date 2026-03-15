<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TLS;

$config = TLS\ClientConfiguration::default()->withAlpnProtocols(['h2', 'http/1.1']);

$tls = TLS\connect('example.com', 443, $config);

$protocol = $tls->getState()->alpnProtocol; // 'h2'
