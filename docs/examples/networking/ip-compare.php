<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IP\Address;

$a = Address::v4('10.0.0.1');
$b = Address::v4('10.0.0.2');

$a->equals($b); // false
$a->compare($b); // Psl\Comparison\Order::Less
$b->compare($a); // Psl\Comparison\Order::Greater

$c = Address::v6('2001:db8::1');
$d = Address::v6('2001:0db8:0000:0000:0000:0000:0000:0001');
$c->equals($d); // true (same bytes, different notation)
