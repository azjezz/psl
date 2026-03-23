<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;
use Psl\DNS\Record;

$resolver = new DNS\SystemResolver();

// Query different record types
$a = $resolver->query('example.com', Record\RecordType::A);
$aaaa = $resolver->query('example.com', Record\RecordType::AAAA);
$mx = $resolver->query('example.com', Record\RecordType::MX);
$txt = $resolver->query('example.com', Record\RecordType::TXT);
$srv = $resolver->query('_http._tcp.example.com', Record\RecordType::SRV);

// Extract typed records from a response
foreach ($a->getAnswerRecords(Record\ARecord::class) as $record) {
    $record->name; // "example.com"
    $record->duration; // TTL as Duration
    $record->address; // Psl\IP\Address
}

foreach ($mx->getAnswerRecords(Record\MXRecord::class) as $record) {
    $record->preference; // int
    $record->exchange; // "mail.example.com"
}

// Reverse lookup
$ptr = $resolver->reverseQuery(\Psl\IP\Address::parse('8.8.8.8'));
foreach ($ptr->getAnswerRecords(Record\PTRRecord::class) as $record) {
    $record->target; // "dns.google"
}
