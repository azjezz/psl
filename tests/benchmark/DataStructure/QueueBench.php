<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\DataStructure;

use PhpBench\Attributes\Groups;
use Psl;
use Psl\DataStructure;
use Psl\Vec;

#[Groups(['ds'])]
final class QueueBench
{
    /**
     * @throws Psl\Exception\InvariantViolationException
     * @throws Psl\DataStructure\Exception\UnderflowException
     * @throws Psl\Vec\Exception\LogicException
     */
    public function benchQueue(): void
    {
        $q = new DataStructure\Queue();

        $q->enqueue(1);
        $q->enqueue(2);
        $q->enqueue(3);
        $q->enqueue(4);

        Psl\invariant(1 === $q->peek(), 'unexpected return value');
        Psl\invariant(1 === $q->peek(), 'unexpected return value');
        Psl\invariant(1 === $q->peek(), 'unexpected return value');
        Psl\invariant(1 === $q->pull(), 'unexpected return value');
        Psl\invariant(2 === $q->pull(), 'unexpected return value');
        Psl\invariant(3 === $q->dequeue(), 'unexpected return value');
        Psl\invariant(1 === $q->count(), 'unexpected return value');
        Psl\invariant(4 === $q->dequeue(), 'unexpected return value');
        Psl\invariant(0 === $q->count(), 'unexpected return value');
        Psl\invariant(null === $q->pull(), 'unexpected return value');

        try {
            $q->dequeue();

            Psl\invariant_violation('expected an exception.');
        } catch (DataStructure\Exception\UnderflowException $e) {
            // O.K.
            Psl\invariant('Cannot dequeue a node from an empty queue.' === $e->getMessage(), "unexpected exception message");
        }

        $data = Vec\range(1, 1000);
        foreach ($data as $elm) {
            $q->enqueue($elm);
        }

        foreach ($data as $elm) {
            Psl\invariant($elm === $q->peek(), 'unexpected return value');
            Psl\invariant($elm === $q->pull(), 'unexpected return value');
        }

        Psl\invariant(null === $q->pull(), 'unexpected return value');
    }
}
