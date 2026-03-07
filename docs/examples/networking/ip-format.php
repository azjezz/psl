<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IP\Address;

$v6 = Address::v6('2001:db8::1');

// Compressed (RFC 5952)
$v6->toString(); // '2001:db8::1'

// Fully expanded
$v6->toExpandedString(); // '2001:0db8:0000:0000:0000:0000:0000:0001'

// Raw binary bytes
$v6->toBytes(); // 16-byte binary string

// Stringable
echo (string) $v6; // '2001:db8::1'
