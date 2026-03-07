<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\CIDR;
use Psl\IP\Address;

$block = new CIDR\Block('192.168.1.0/24');

// Using a string
$block->contains('192.168.1.100'); // true

// Using an Address object
$addr = Address::parse('192.168.1.100');
$block->contains($addr); // true
