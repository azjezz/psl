<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class OrdTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testOrd(int $expected, string $value): void
    {
        static::assertSame($expected, Str\ord($value));
    }

    public static function provideData(): array
    {
        return [
            [1605, 'م'],
            [48, '0'],
            [38, '&'],
            [1575, 'ا'],
            [65, 'A'],
        ];
    }
}
