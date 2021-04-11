<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Str;

final class MetaphoneTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMetaphone(?string $expected, string $str, int $phonemes = 0): void
    {
        static::assertSame($expected, Str\metaphone($str, $phonemes));
    }

    public function provideData(): array
    {
        return [
            ['HL', 'hello'],
            ['HLWRLT', 'Hello, World !!', 10],
            ['PPLLNKTBTSTNWLLWSFNTX0R', 'People linked by destiny will always find each other.'],
            ['', 'سيف'],
            ['', '1337'],
        ];
    }

    public function testThrowsIfPhonemeIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected non-negative phonemes, got -1.');

        Str\metaphone('foo', -1);
    }
}
