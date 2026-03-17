<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class ReverseTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReverse(string $expected, string $input): void
    {
        static::assertSame($expected, Byte\reverse($input));
    }

    public static function provideData(): array
    {
        return [
            ['oof', 'foo'],
            ['', ''],
        ];
    }
}
