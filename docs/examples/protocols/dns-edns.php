<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;
use Psl\DNS\EDNS;
use Psl\DNS\Record\RecordType;
use Psl\IP;
use Psl\SecureRandom;

$resolver = new DNS\SystemResolver();

// Query with EDNS0 options
$response = $resolver->query('example.com', RecordType::A, ednsOptions: [
    new EDNS\CookieOption(clientCookie: SecureRandom\bytes(8)),
    new EDNS\ECSOption(IP\Address::parse('203.0.113.0'), sourcePrefixLength: 24, scopePrefixLength: 0),
    new EDNS\PaddingOption(128),
    new EDNS\NSIDOption(),
]);
