<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\CIDR;

$loopback = new CIDR\Block('::1/128');
$loopback->contains('::1'); // true
