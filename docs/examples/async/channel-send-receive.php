<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Channel;

[$receiver, $sender] = Channel\bounded(3);

$sender->send('hello');
$sender->send('world');

$receiver->receive(); // 'hello'
$receiver->receive(); // 'world'
