<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

final class ReplaceCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReplaceCi(string $expected, string $haystack, string $needle, string $replacement): void
    {
        static::assertSame($expected, Str\replace_ci($haystack, $needle, $replacement));
    }

    public function provideData(): array
    {
        return [
            ['Hello, World!', 'Hello, you!', 'You', 'World', ],
            ['Hello, World!', 'Hello, You!', 'You', 'World', ],
            ['مرحبا بكم', 'مرحبا سيف', 'سيف', 'بكم'],
            ['foo', 'foo', 'bar', 'baz'],
        ];
    }

    /**
     * @dataProvider provideBadUtf8Data
     */
    public function testBadUtf8(string $string, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Str\replace_ci($string, $string, $string);
    }

    public function provideBadUtf8Data(): iterable
    {
        yield [
            "\xc1\xbf",
            InvariantViolationException::class,
            'Expected $needle to be a valid UTF-8 string.',
        ];

        yield [
            "\xe0\x81\xbf",
            InvariantViolationException::class,
            'Expected $needle to be a valid UTF-8 string.',
        ];

        yield [
            "\xf0\x80\x81\xbf",
            InvariantViolationException::class,
            'Expected $needle to be a valid UTF-8 string.',
        ];
    }
}
