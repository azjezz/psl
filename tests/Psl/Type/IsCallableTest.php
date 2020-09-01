<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Type;

class IsCallableTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIsCallable(bool $expected, $value): void
    {
        self::assertSame($expected, Type\is_callable($value));
    }

    public function provideData(): iterable
    {
        yield [true, fn () => 'hello'];
        yield [true, [$this, 'provideData']];
        yield [true, 'Psl\Type\is_callable'];
        yield [false, [$this, 'non_existent_method']];
        yield [false, ['non_existent_class', 'static_method']];
    }
}
