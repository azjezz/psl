<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Exception;

/**
 * @covers \Psl\Arr\random
 */
class RandomTest extends TestCase
{
    public function testRandom(): void
    {
        $values = ['a', 'b', 'c'];
        $value = Arr\random($values);

        static::assertNotNull($value);
        static::assertContains($value, $values);
    }

    public function testRandomThrowsWhenTheGivenArrayIsEmpty(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected non-empty-array.');

        Arr\random([]);
    }
}
