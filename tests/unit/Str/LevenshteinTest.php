<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class LevenshteinTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testLevenshtein(
        int $expected,
        string $a,
        string $b,
        null|int $coi = null,
        null|int $cor = null,
        null|int $cod = null,
    ): void {
        static::assertSame($expected, Str\levenshtein($a, $b, $coi, $cor, $cod));
    }

    public static function provideData(): array
    {
        return [
            [0, 'o', 'o'],
            [1, 'foo', 'oo'],
            [1, 'oo', 'foo'],
            [6, 'saif', 'azjezz'],
            [48, 'saif', 'azjezz', 9, 8, 5],
        ];
    }
}
