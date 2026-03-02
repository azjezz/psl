<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\CIDR;

$exact = new CIDR\Block('192.168.1.1/32');
$exact->contains('192.168.1.1'); // true
$exact->contains('192.168.1.2'); // false
