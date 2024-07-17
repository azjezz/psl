<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection\Set;

final class SetTest extends AbstractSetTest
{
    /**
     * The Set class used for values, keys .. etc.
     *
     * @var class-string<Set>
     */
    protected string $setClass = Set::class;

    /**
     * @template T of array-key
     *
     * @param array<T, mixed> $items
     *
     * @return Set<T>
     */
    protected function createFromList(array $items): Set
    {
        return Set::fromArray($items);
    }
}
