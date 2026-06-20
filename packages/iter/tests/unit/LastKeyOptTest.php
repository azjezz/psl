<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class LastKeyOptTest extends TestCase
{
    #[DataProvider('provideDataSome')]
    public function testLastKeySome(int|array $expected, iterable $iterable): void
    {
        $result = Iter\last_key_opt::<int|array, int|null|string>($iterable);

        static::assertSame($expected, $result->unwrap());
    }

    public static function provideDataSome(): iterable
    {
        yield [3, [1, 2, 3, 4]];
        yield [3, Iter\to_iterator::<int, int>([1, 2, 3, 4])];
        yield [3, Vec\range::<int>(1, 4)];
        yield [4, Vec\range::<int>(4, 8)];
        yield [4, Iter\to_iterator::<int, int>(Vec\range::<int>(4, 8))];
        yield [0, [null]];
        yield [1, [null, null]];
        yield [[1, 2], (static fn(): iterable => yield [1, 2] => 'hello')()];
    }

    #[DataProvider('provideDataNone')]
    public function testLastKeyNone(iterable $iterable): void
    {
        $result = Iter\last_key_opt::<int|array, int|null|string>($iterable);

        static::assertTrue($result->isNone());
    }

    public static function provideDataNone(): iterable
    {
        yield [[]];
    }
}
