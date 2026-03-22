<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\H2;

/** @var H2\ServerConnectionInterface $server */

// Alt-Svc (RFC 7838) advertises alternative service endpoints,
// enabling HTTP/2 to HTTP/3 migration.
$server->sendAltSvc(0, 'https://example.com', 'h3=":443"; ma=2592000');

// ORIGIN (RFC 8336) declares which origins the server is authoritative for,
// enabling connection coalescing across multiple domains.
$server->sendOrigin([
    'https://example.com',
    'https://cdn.example.com',
    'https://api.example.com',
]);
