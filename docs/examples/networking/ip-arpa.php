<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IP\Address;

$v4 = Address::v4('192.168.1.10');
$v4->toArpaName(); // '10.1.168.192.in-addr.arpa'

$v6 = Address::v6('2001:db8::1');
$v6->toArpaName(); // '1.0.0.0.0.0.0.0...8.b.d.0.1.0.0.2.ip6.arpa'
