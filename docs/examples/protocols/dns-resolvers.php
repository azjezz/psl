<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;
use Psl\TLS;

// UDP resolver (standard DNS, port 53)
$udp = new DNS\UDPResolver('8.8.8.8');

// TCP resolver with connection pooling
$tcp = new DNS\TCPResolver('8.8.8.8');

// DNS-over-TLS (port 853)
$dot = new DNS\TCPResolver('8.8.8.8', port: 853, tlsClientConfiguration: new TLS\ClientConfiguration());

// UDP with automatic TCP fallback on truncated responses
$fallback = new DNS\FallbackResolver([$udp, $tcp]);

// Race multiple nameservers, return the fastest response
$racing = new DNS\RacingResolver([
    new DNS\UDPResolver('8.8.8.8'),
    new DNS\UDPResolver('1.1.1.1'),
]);
