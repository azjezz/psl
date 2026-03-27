<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;
use Psl\DNS\Record\RecordType;

// BlockingSystemResolver wraps PHP's dns_get_record() for synchronous,
// zero-overhead DNS resolution. No event loop, no fibers, no connection pooling.
$resolver = new DNS\BlockingSystemResolver();

// Query A records
$response = $resolver->query('example.com', RecordType::A);
foreach ($response->answers as $record) {
    /** @var DNS\Record\ARecord $record */
    $record->address->toString();
    $record->duration->getSeconds();
}

// Query MX records
$response = $resolver->query('example.com', RecordType::MX);
foreach ($response->answers as $record) {
    /** @var DNS\Record\MXRecord $record */
    $record->exchange;
    $record->preference;
}

// Non-existent domain returns NXDOMAIN, never throws
$response = $resolver->query('nonexistent.invalid', RecordType::A);
$response->code; // ResponseCode::NonExistentDomain

// Reverse lookup via PTR
$response = $resolver->reverseQuery(Psl\IP\Address::parse('8.8.8.8'));
foreach ($response->answers as $record) {
    /** @var DNS\Record\PTRRecord $record */
    $record->target; // "dns.google"
}
