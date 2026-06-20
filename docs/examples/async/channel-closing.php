<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Channel;
use Psl\IO;

[$receiver, $sender] = Channel\bounded::<string>(10);

$sender->send('last message');
$sender->close();

$sender->isClosed(); // true
$receiver->receive(); // 'last message'

try {
    $receiver->receive(); // throws ClosedChannelException -- no more messages
} catch (Channel\Exception\ClosedChannelException) {
    IO\write_line('Channel is closed, cannot receive more messages');
}
