<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

final class ConvertEncodingTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testConvertEncoding(
        ?string $expected,
        string $string,
        string $from_encoding,
        string $to_encoding
    ): void {
        self::assertSame($expected, Str\convert_encoding($string, $from_encoding, $to_encoding));
    }

    public function provideData(): array
    {
        return [
            ['Ã¥Ã¤Ã¶', 'åäö', 'ISO-8859-1', 'UTF-8'],
        ];
    }

    public function testConvertEncodingThrowsForInvalidFromEncoding(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('$from_encoding is invalid.');

        Str\convert_encoding('Hello, World!', 'foobar', 'UTF-8');
    }

    public function testConvertEncodingThrowsForInvalidToEncoding(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('$to_encoding is invalid.');

        Str\convert_encoding('Hello, World!', 'ASCII', 'UTF-1337');
    }
}
