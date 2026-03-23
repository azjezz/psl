<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;

// Route queries for internal domains to a private nameserver,
// everything else to a public resolver
$resolver = new DNS\SplitHorizonResolver(routes: [
    new DNS\Route(domains: ['corp.internal', 'dev.internal'], resolver: new DNS\UDPResolver('10.0.0.53')),
], default: new DNS\SystemResolver());

// Uses the private nameserver (matches corp.internal)
$resolver->query('db.corp.internal', DNS\Record\RecordType::A);

// Uses the system resolver (no matching route)
$resolver->query('example.com', DNS\Record\RecordType::A);
