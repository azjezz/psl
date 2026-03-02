<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Socks;

// Note: This example requires a running SOCKS5 proxy server with authentication.
$connector = new Socks\Connector('proxy.example.com', 1080, 'user', 'pass');
