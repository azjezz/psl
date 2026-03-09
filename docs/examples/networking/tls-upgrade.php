<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TCP;
use Psl\TLS;

// Two-step: connect TCP first, then upgrade
$stream = TCP\connect('example.com', 443);
$tls = TLS\Connector::default()->connect($stream, 'example.com');
