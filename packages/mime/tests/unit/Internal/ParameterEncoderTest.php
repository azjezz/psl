<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\MIME\Internal\MediaTypeParser;
use Psl\MIME\Internal\ParameterEncoder;
use Psl\Str;

final class ParameterEncoderTest extends TestCase
{
    public function testAsciiValueNotEncoded(): void
    {
        $pairs = ParameterEncoder::encode('filename', 'report.pdf');

        static::assertCount(1, $pairs);
        static::assertSame('filename', $pairs[0][0]);
        static::assertSame('report.pdf', $pairs[0][1]);
    }

    public function testNonAsciiValueEncoded(): void
    {
        $pairs = ParameterEncoder::encode('filename', 'résumé.pdf');

        static::assertCount(1, $pairs);
        static::assertSame('filename*', $pairs[0][0]);
        static::assertStringStartsWith("utf-8''", $pairs[0][1]);
        static::assertStringContainsString('%', $pairs[0][1]);
    }

    public function testEncodedValueDecodesCorrectly(): void
    {
        $pairs = ParameterEncoder::encode('filename', 'café.txt');

        static::assertCount(1, $pairs);
        static::assertSame('filename*', $pairs[0][0]);
        static::assertSame("utf-8''caf%c3%a9.txt", $pairs[0][1]);
    }

    public function testUnreservedCharsNotEncoded(): void
    {
        $pairs = ParameterEncoder::encode('test', 'ABCdef-._~123');

        static::assertCount(1, $pairs);
        static::assertSame('test', $pairs[0][0]);
        static::assertSame('ABCdef-._~123', $pairs[0][1]);
    }

    public function testSpaceIsNotRfc2231Encoded(): void
    {
        $pairs = ParameterEncoder::encode('filename', 'hello world.txt');

        static::assertCount(1, $pairs);
        static::assertSame('filename', $pairs[0][0]);
        static::assertSame('hello world.txt', $pairs[0][1]);
    }

    public function testControlCharacterIsEncoded(): void
    {
        $pairs = ParameterEncoder::encode('test', "hello\x01world");

        static::assertCount(1, $pairs);
        static::assertSame('test*', $pairs[0][0]);
        static::assertStringContainsString('%01', $pairs[0][1]);
    }

    public function testEmptyValueNotEncoded(): void
    {
        $pairs = ParameterEncoder::encode('test', '');

        static::assertCount(1, $pairs);
        static::assertSame('test', $pairs[0][0]);
        static::assertSame('', $pairs[0][1]);
    }

    public function testHighByteCharacters(): void
    {
        $pairs = ParameterEncoder::encode('filename', "\xC3\xBC" . 'ber.txt');

        static::assertCount(1, $pairs);
        static::assertSame('filename*', $pairs[0][0]);
        static::assertSame("utf-8''%c3%bcber.txt", $pairs[0][1]);
    }

    public function testLongValueSplitIntoContinuations(): void
    {
        $longName = Str\repeat("\xC3\xA9", 50);
        $pairs = ParameterEncoder::encode('filename', $longName);

        static::assertGreaterThan(1, Iter\count($pairs));

        static::assertStringStartsWith('filename*0*', $pairs[0][0]);
        static::assertStringStartsWith("utf-8''", $pairs[0][1]);

        for ($i = 1; $i < Iter\count($pairs); $i++) {
            static::assertSame('filename*' . $i . '*', $pairs[$i][0]);
            static::assertStringNotContainsString("utf-8''", $pairs[$i][1]);
        }
    }

    public function testContinuationsRoundTrip(): void
    {
        $original = Str\repeat("\xC3\xA9\xC3\xBC\xC3\xB6", 30);
        $pairs = ParameterEncoder::encode('filename', $original);

        static::assertGreaterThan(1, Iter\count($pairs));

        $paramString = '';
        foreach ($pairs as [$name, $value]) {
            $paramString .= '; ' . $name . '=' . $value;
        }

        $parsed = MediaTypeParser::parseParameters($paramString);
        static::assertCount(1, $parsed);
        static::assertSame('filename', $parsed[0][0]);
        static::assertSame($original, $parsed[0][1]);
    }

    public function testPureAsciiNotEncoded(): void
    {
        $pairs = ParameterEncoder::encode('name', 'hello-world.txt');

        static::assertCount(1, $pairs);
        static::assertSame('name', $pairs[0][0]);
        static::assertSame('hello-world.txt', $pairs[0][1]);
    }

    public function testControlCharEncoded(): void
    {
        $pairs = ParameterEncoder::encode('name', "tab\x09here");

        static::assertCount(1, $pairs);
        static::assertSame('name*', $pairs[0][0]);
    }

    public function testSpaceInValueNotRfc2231Encoded(): void
    {
        $pairs = ParameterEncoder::encode('name', 'hello world');

        static::assertCount(1, $pairs);
        static::assertSame('name', $pairs[0][0]);
        static::assertSame('hello world', $pairs[0][1]);
    }

    public function testLongAsciiValueNotSplit(): void
    {
        $longValue = Str\repeat('a', 200);
        $pairs = ParameterEncoder::encode('name', $longValue);

        static::assertCount(1, $pairs);
        static::assertSame('name', $pairs[0][0]);
        static::assertSame($longValue, $pairs[0][1]);
    }

    public function testEncodedValueContainsCharsetPrefix(): void
    {
        $pairs = ParameterEncoder::encode('name', "\xC3\xA9");

        static::assertSame("utf-8''%c3%a9", $pairs[0][1]);
    }

    public function testMultipleContinuationsNumbered(): void
    {
        $longUtf8 = Str\repeat("\xC3\xA9", 100);
        $pairs = ParameterEncoder::encode('f', $longUtf8);

        for ($i = 0; $i < Iter\count($pairs); $i++) {
            static::assertStringStartsWith('f*' . $i . '*', $pairs[$i][0]);
        }
    }
}
