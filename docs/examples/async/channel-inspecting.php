<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Channel;

[$receiver, $sender] = Channel\bounded::<string>(5);

$sender->getCapacity(); // 5
$sender->count(); // 0 (number of messages currently in the channel)
$sender->isEmpty(); // true
$sender->isFull(); // false
$sender->isClosed(); // false
