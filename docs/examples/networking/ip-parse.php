<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IP\Address;

// Parse with explicit family
$v4 = Address::v4('192.168.1.1');
$v6 = Address::v6('2001:db8::1');

// Auto-detect family
$auto = Address::parse('::1');
$auto->family; // Family::V6

// Create from raw binary bytes
$fromBytes = Address::fromBytes("\xc0\xa8\x01\x01");
$fromBytes->toString(); // '192.168.1.1'
