<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\CIDR;

$block = new CIDR\Block('192.168.1.0/24');
$block->contains('192.168.1.100'); // true
$block->contains('192.168.2.1'); // false
