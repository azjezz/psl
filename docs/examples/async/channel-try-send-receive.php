<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Channel;
use Psl\IO;

[$receiver, $sender] = Channel\bounded::<string>(1);

$sender->trySend('first');

try {
    $sender->trySend('second'); // throws FullChannelException -- channel is at capacity
} catch (Channel\Exception\FullChannelException) {
    IO\write_line('Channel is full, cannot send "second"');
}

$receiver->tryReceive(); // 'first'

try {
    $receiver->tryReceive(); // throws EmptyChannelException -- nothing left
} catch (Channel\Exception\EmptyChannelException) {
    IO\write_line('Channel is empty, cannot receive');
}
