<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IP\Address;

$loopback = Address::v4('127.0.0.1');
$loopback->isLoopback(); // true

$private = Address::v4('10.0.0.1');
$private->isPrivate(); // true

$public = Address::v4('8.8.8.8');
$public->isGlobalUnicast(); // true
$public->isPrivate(); // false

$doc = Address::v6('2001:db8::1');
$doc->isDocumentation(); // true
$doc->isGlobalUnicast(); // false
