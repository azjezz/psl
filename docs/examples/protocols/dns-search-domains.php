<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;
use Psl\DNS\Record\RecordType;

// Expand short names by appending search domains
$resolver = new DNS\SearchDomainResolver(
    inner: new DNS\SystemResolver(),
    searchDomains: ['example.com', 'corp.example.com'],
);

// Queries "db.example.com", then "db.corp.example.com" if NXDOMAIN
$resolver->query('db', RecordType::A);

// Fully qualified names skip expansion
$resolver->query('google.com', RecordType::A);
