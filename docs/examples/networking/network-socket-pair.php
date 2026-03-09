<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Network;

[$a, $b] = Network\socket_pair();
$a->writeAll('ping');
$a->shutdown();
echo $b->readAll(); // "ping"
