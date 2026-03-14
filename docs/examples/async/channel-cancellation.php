<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Channel;
use Psl\DateTime\Duration;
use Psl\IO;

/** @var Channel\ReceiverInterface<string> $receiver */
/** @var Channel\SenderInterface<string> $sender */
[$receiver, $sender] = Channel\bounded(1);

// Producer sends one message then stops
Async\run(static function () use ($sender): void {
    $sender->send('hello');
    Async\sleep(Duration::seconds(5));
    $sender->send('never arrives');
})->ignore();

// Consumer receives with a timeout
$token = new Async\TimeoutCancellationToken(Duration::milliseconds(50));

$first = $receiver->receive($token);
IO\write_line('Received: %s', $first);

try {
    // This will be cancelled — no second message within 50ms
    $receiver->receive($token);
} catch (Async\Exception\CancelledException) {
    IO\write_line('Timed out waiting for next message');
}
