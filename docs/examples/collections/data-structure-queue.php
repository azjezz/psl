<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DataStructure;

$queue = new DataStructure\Queue::<string>();

$queue->enqueue('first');
$queue->enqueue('second');
$queue->enqueue('third');

$queue->count(); // 3

$queue->peek(); // "first" (does not remove)
$queue->pull(); // "first" (removes and returns)
$queue->dequeue(); // "second" (removes and returns, throws if empty)
