<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class OrdTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testOrd(int $expected, string $value): void
    {
        static::assertSame($expected, Byte\ord($value));
    }

    public function provideData(): array
    {
        return [
            [217, 'م'],
            [48, '0'],
            [38, '&'],
            [216, 'ا'],
            [65, 'A'],
        ];
    }
}
