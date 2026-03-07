<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IP\Address;

$addr = Address::parse('192.168.1.10');
$addr->family; // Family::V4
$addr->toString(); // '192.168.1.10'
$addr->isPrivate(); // true
$addr->isGlobalUnicast(); // false
