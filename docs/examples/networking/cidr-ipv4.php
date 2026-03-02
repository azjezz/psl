<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\CIDR;

$private = new CIDR\Block('10.0.0.0/8');
$private->contains('10.1.2.3'); // true
$private->contains('172.16.0.1'); // false
