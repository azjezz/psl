<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;
use Psl\TCP;
use Psl\TLS;

// Encrypt all system DNS traffic with DNS-over-TLS.
// udp: false ensures no plaintext UDP queries are sent.
// The TLS connector wraps every TCP connection in TLS (DoT, RFC 7858).
// Nameserver addresses are still read from the OS configuration.
$resolver = new DNS\SystemResolver(
    udp: false,
    connector: new TLS\TCPConnector(new TCP\Connector(), new TLS\Connector(new TLS\ClientConfiguration())),
);
