<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\DataStructure;

use PhpBench\Attributes\Groups;
use Psl;
use Psl\DataStructure;
use Psl\Vec;

#[Groups(['ds', 'pq'])]
final class PriorityQueueBench
{
    /**
     * @throws Psl\Exception\InvariantViolationException
     * @throws Psl\DataStructure\Exception\UnderflowException
     * @throws Psl\Vec\Exception\LogicException
     */
    public function benchQueue(): void
    {
        $queue = new DataStructure\PriorityQueue();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 2);
        $queue->enqueue('hello', 3);

        Psl\invariant(3 === $queue->count(), "unexpected return value");
        Psl\invariant('hello' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(2 === $queue->count(), "unexpected return value");
        Psl\invariant('hey' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(1 === $queue->count(), "unexpected return value");
        Psl\invariant('hi' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(0 === $queue->count(), "unexpected return value");

        $queue = new DataStructure\PriorityQueue();
        $queue->enqueue('hi');
        $queue->enqueue('hey');
        $queue->enqueue('hello');

        Psl\invariant(3 === $queue->count(), "unexpected return value");
        Psl\invariant('hi' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(2 === $queue->count(), "unexpected return value");
        Psl\invariant('hey' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(1 === $queue->count(), "unexpected return value");
        Psl\invariant('hello' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(0 === $queue->count(), "unexpected return value");

        $queue->enqueue('hi');
        $queue->enqueue('hey');
        $queue->enqueue('hello');

        Psl\invariant(3 === $queue->count(), "unexpected return value");
        Psl\invariant('hi' === $queue->pull(), "unexpected return value");
        Psl\invariant(2 === $queue->count(), "unexpected return value");
        Psl\invariant('hey' === $queue->pull(), "unexpected return value");
        Psl\invariant(1 === $queue->count(), "unexpected return value");
        Psl\invariant('hello' === $queue->pull(), "unexpected return value");
        Psl\invariant(0 === $queue->count(), "unexpected return value");

        $queue = new DataStructure\PriorityQueue();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 1);
        $queue->enqueue('hello', 1);

        Psl\invariant(3 === $queue->count(), "unexpected return value");
        Psl\invariant('hi' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(2 === $queue->count(), "unexpected return value");
        Psl\invariant('hey' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(1 === $queue->count(), "unexpected return value");
        Psl\invariant('hello' === $queue->dequeue(), "unexpected return value");
        Psl\invariant(0 === $queue->count(), "unexpected return value");
        Psl\invariant(null === $queue->peek(), "unexpected return value");
        
        $queue = new DataStructure\PriorityQueue();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 2);
        $queue->enqueue('hello', 3);

        Psl\invariant(3 === $queue->count(), "unexpected return value");
        Psl\invariant('hello' === $queue->peek(), "unexpected return value");
        Psl\invariant(3 === $queue->count(), "unexpected return value");
        Psl\invariant('hello' === $queue->peek(), "unexpected return value");

        $queue = new DataStructure\PriorityQueue();
        $data = Vec\range(1, 1000);
        foreach ($data as $elm) {
            $queue->enqueue($elm);
        }

        foreach ($data as $elm) {
            Psl\invariant($elm === $queue->peek(), "unexpected return value");
            Psl\invariant($elm === $queue->pull(), "unexpected return value");
        }

        Psl\invariant(null === $queue->pull(), "unexpected return value");
        
        try {
            $queue->dequeue();

            Psl\invariant_violation('expected an exception.');
        } catch (DataStructure\Exception\UnderflowException $e) {
            // O.K.
            Psl\invariant('Cannot dequeue a node from an empty queue.' === $e->getMessage(), "unexpected exception message");
        }
    }
}
