<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Iter;

class RandomTest extends TestCase
{
    public function testRandom(): void
    {
        $iterable = [1, 2, 3, 4, 5];
        $value = Iter\random($iterable);
        
        self::assertTrue(Iter\contains($iterable, $value));

        $iterable = Iter\to_iterator([1, 2, 3, 4, 5]);
        $value = Iter\random($iterable);
        
        self::assertTrue(Iter\contains($iterable, $value));
    }

    public function testRandomWithEmptyIterator(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-empty iterable.');

        Iter\random([]);
    }
}
