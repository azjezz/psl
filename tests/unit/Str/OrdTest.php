<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class OrdTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testOrd(int $expected, string $value): void
    {
        static::assertSame($expected, Str\ord($value));
    }

    public function provideData(): array
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
