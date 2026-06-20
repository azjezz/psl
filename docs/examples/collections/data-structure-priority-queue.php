<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DataStructure;

$pq = new DataStructure\PriorityQueue::<string>();

$pq->enqueue('low-priority task', priority: 1);
$pq->enqueue('high-priority task', priority: 10);
$pq->enqueue('medium-priority task', priority: 5);
$pq->enqueue('another high-priority task', priority: 10);

$pq->dequeue(); // "high-priority task" (priority 10, enqueued first)
$pq->dequeue(); // "another high-priority task" (priority 10, enqueued second)
$pq->dequeue(); // "medium-priority task" (priority 5)
$pq->dequeue(); // "low-priority task" (priority 1)
