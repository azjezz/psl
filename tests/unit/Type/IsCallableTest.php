<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Type;

final class IsCallableTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIsCallable(bool $expected, $value): void
    {
        static::assertSame($expected, Type\is_callable($value));
    }

    public function provideData(): iterable
    {
        yield [true, static fn () => 'hello'];
        yield [true, [$this, 'provideData']];
        yield [true, 'Psl\Type\is_callable'];
        yield [false, [$this, 'non_existent_method']];
        yield [false, ['non_existent_class', 'static_method']];
    }
}
