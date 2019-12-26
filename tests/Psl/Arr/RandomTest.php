<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Exception;

class RandomTest extends TestCase
{
    public function testRandom(): void
    {
        $values = ['a', 'b', 'c'];
        $value = Arr\random($values);

        static::assertNotNull($value);
        static::assertContains($value, $values);
    }

    public function testRandomWithOneItem(): void
    {
        $values = ['a'];
        $value = Arr\random($values);

        static::assertSame('a', $value);
    }

    public function testRandomThrowsWhenTheGivenArrayIsEmpty(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected non-empty-array.');

        Arr\random([]);
    }
}
