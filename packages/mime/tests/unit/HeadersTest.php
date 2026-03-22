<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\MIME\Headers;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;
use Stringable;

use function substr_count;

final class HeadersTest extends TestCase
{
    public function testFromPairsBasic(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type',   'text/html'],
            ['Content-Length', '42'],
        ]);

        static::assertSame(2, $headers->count());
    }

    public function testGetFindsFirstValue(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
            ['Content-Type', 'text/plain'],
        ]);

        static::assertSame('text/html', $headers->get('Content-Type'));
    }

    public function testGetCaseInsensitive(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertSame('text/html', $headers->get('content-type'));
        static::assertSame('text/html', $headers->get('CONTENT-TYPE'));
        static::assertSame('text/html', $headers->get('Content-Type'));
    }

    public function testGetReturnsNullForMissing(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertNull($headers->get('Accept'));
    }

    public function testHasTrue(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertTrue($headers->has('Content-Type'));
    }

    public function testHasFalse(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertFalse($headers->has('Accept'));
    }

    public function testHasCaseInsensitive(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertTrue($headers->has('content-type'));
        static::assertTrue($headers->has('CONTENT-TYPE'));
    }

    public function testAllReturnsAllValues(): void
    {
        $headers = Headers::fromPairs([
            ['Accept',       'text/html'],
            ['Content-Type', 'text/plain'],
            ['Accept',       'application/json'],
        ]);

        static::assertSame(['text/html', 'application/json'], $headers->all('Accept'));
    }

    public function testAllCaseInsensitive(): void
    {
        $headers = Headers::fromPairs([
            ['Accept', 'text/html'],
            ['ACCEPT', 'application/json'],
        ]);

        static::assertSame(['text/html', 'application/json'], $headers->all('accept'));
    }

    public function testAllEmptyForMissing(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertSame([], $headers->all('Accept'));
    }

    public function testPairsPreservesOriginalCase(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type',    'text/html'],
            ['X-Custom-Header', 'value'],
        ]);

        $pairs = $headers->pairs();

        static::assertSame(['Content-Type', 'text/html'], $pairs[0]);
        static::assertSame(['X-Custom-Header', 'value'], $pairs[1]);
    }

    public function testCountEmpty(): void
    {
        $headers = Headers::default();

        static::assertSame(0, $headers->count());
    }

    public function testCount(): void
    {
        $headers = Headers::fromPairs([
            ['A', '1'],
            ['B', '2'],
            ['C', '3'],
        ]);

        static::assertSame(3, $headers->count());
    }

    public function testCountFunction(): void
    {
        $headers = Headers::fromPairs([
            ['A', '1'],
            ['B', '2'],
        ]);

        static::assertCount(2, $headers);
    }

    public function testEmptySingleton(): void
    {
        static::assertSame(Headers::default(), Headers::default());
    }

    public function testEmptyHasZeroCount(): void
    {
        $headers = Headers::default();

        static::assertSame(0, $headers->count());
        static::assertSame([], $headers->pairs());
        static::assertSame('', $headers->toString());
    }

    public function testDefaultReturnsEmpty(): void
    {
        $headers = Headers::default();

        static::assertSame(0, $headers->count());
    }

    public function testGetIteratorYieldsPairs(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
            ['Accept',       'application/json'],
        ]);

        $collected = [];
        foreach ($headers as $pair) {
            $collected[] = $pair;
        }

        static::assertSame(
            [
                ['Content-Type', 'text/html'],
                ['Accept',       'application/json'],
            ],
            $collected,
        );
    }

    public function testToStringSingleHeader(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertSame("Content-Type: text/html\r\n", $headers->toString());
    }

    public function testToStringMultipleHeaders(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type',   'text/html'],
            ['Content-Length', '42'],
        ]);

        static::assertSame("Content-Type: text/html\r\nContent-Length: 42\r\n", $headers->toString());
    }

    public function testToStringEmpty(): void
    {
        static::assertSame('', Headers::default()->toString());
    }

    public function testMagicToString(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
        ]);

        static::assertSame("Content-Type: text/html\r\n", (string) $headers);
    }

    public function testStringable(): void
    {
        $headers = Headers::fromPairs([
            ['X-Test', 'value'],
        ]);

        static::assertInstanceOf(Stringable::class, $headers);
        static::assertSame($headers->toString(), (string) $headers);
    }

    public function testToFoldedStringShortHeaders(): void
    {
        $headers = Headers::fromPairs([
            ['From', 'test@example.com'],
        ]);

        static::assertSame("From: test@example.com\r\n", $headers->toFoldedString());
    }

    public function testToFoldedStringLongValue(): void
    {
        $value = 'This is a very long subject line that definitely exceeds the seventy-eight character limit and should be folded';
        $headers = Headers::fromPairs([
            ['Subject', $value],
        ]);

        $folded = $headers->toFoldedString();
        $lines = Byte\split($folded, "\r\n");

        static::assertGreaterThan(1, Iter\count(Vec\filter($lines, static fn(string $l): bool => $l !== '')));
    }

    public function testToFoldedStringMultipleHeaders(): void
    {
        $headers = Headers::fromPairs([
            [
                'Subject',
                'This is a very long subject line that definitely exceeds the seventy-eight character limit and should be folded',
            ],
            ['From', 'test@example.com'],
        ]);

        $folded = $headers->toFoldedString();

        static::assertStringContainsString("From: test@example.com\r\n", $folded);
        static::assertStringContainsString("\r\n ", $folded);
    }

    public function testToFoldedStringCustomLimits(): void
    {
        $headers = Headers::fromPairs([
            ['Subject', 'This value is longer than forty characters for sure'],
        ]);

        $folded = $headers->toFoldedString(40);
        $lines = Byte\split($folded, "\r\n");

        static::assertGreaterThan(1, Iter\count(Vec\filter($lines, static fn(string $l): bool => $l !== '')));
    }

    public function testToFoldedStringSingleLongToken(): void
    {
        $token = Str\repeat('x', 100);
        $headers = Headers::fromPairs([
            ['Subject', $token],
        ]);

        $folded = $headers->toFoldedString();

        static::assertSame('Subject: ' . $token . "\r\n", $folded);
    }

    public function testToFoldedStringMultipleFolds(): void
    {
        $words = [];
        for ($i = 0; $i < 40; $i++) {
            $words[] = 'longword' . $i;
        }

        $value = Str\join($words, ' ');
        $headers = Headers::fromPairs([
            ['Subject', $value],
        ]);

        $folded = $headers->toFoldedString();
        $foldCount = Iter\count(Byte\split($folded, "\r\n ")) - 1;

        static::assertGreaterThanOrEqual(2, $foldCount);
    }

    public function testToFoldedStringPreservesShort(): void
    {
        $headers = Headers::fromPairs([
            ['X-Short', 'val'],
        ]);

        static::assertSame("X-Short: val\r\n", $headers->toFoldedString());
    }

    public function testToFoldedStringDefaultParams(): void
    {
        $headers = Headers::fromPairs([
            ['From', 'test@example.com'],
        ]);

        static::assertSame($headers->toFoldedString(78, 998), $headers->toFoldedString());
    }

    public function testToFoldedStringEmpty(): void
    {
        static::assertSame('', Headers::default()->toFoldedString());
    }

    public function testToFoldedStringHeaderNameCountsTowardLimit(): void
    {
        $value = 'a b c d e f g h i j k l m n o p q r s t u v w x y z';
        $shortName = Headers::fromPairs([
            ['X', $value],
        ]);
        $longName = Headers::fromPairs([
            ['X-Very-Long-Header-Name', $value],
        ]);

        $shortFolded = $shortName->toFoldedString();
        $longFolded = $longName->toFoldedString();

        $shortFoldCount = substr_count($shortFolded, "\r\n ");
        $longFoldCount = substr_count($longFolded, "\r\n ");

        static::assertGreaterThanOrEqual($shortFoldCount, $longFoldCount);
    }

    public function testToFoldedStringExactSoftLimitNoFold(): void
    {
        $headers = Headers::fromPairs([
            ['X', Str\repeat('a', 50) . ' ' . Str\repeat('b', 24)],
        ]);

        $folded = $headers->toFoldedString();

        static::assertSame(78, Byte\length(Byte\strip_suffix($folded, "\r\n")));
        static::assertStringNotContainsString("\r\n ", $folded);
    }

    public function testToFoldedStringOnePastSoftLimitFolds(): void
    {
        $headers = Headers::fromPairs([
            ['X', Str\repeat('a', 50) . ' ' . Str\repeat('b', 25)],
        ]);

        $folded = $headers->toFoldedString();

        static::assertStringContainsString("\r\n ", $folded);
    }

    public function testToFoldedStringHardLimitBoundaryNotChunked(): void
    {
        $token = Str\repeat('x', 997);
        $headers = Headers::fromPairs([
            ['X', 'short ' . $token],
        ]);

        $folded = $headers->toFoldedString();

        static::assertStringContainsString($token, $folded);
    }

    public function testToFoldedStringHardLimitBoundaryChunked(): void
    {
        $token = Str\repeat('x', 998);
        $headers = Headers::fromPairs([
            ['X', 'short ' . $token],
        ]);

        $folded = $headers->toFoldedString();

        static::assertStringNotContainsString($token, $folded);
    }

    public function testToFoldedStringSplitsOnTabs(): void
    {
        $headers = Headers::fromPairs([
            ['Subject', "word1\tword2\tword3 word4"],
        ]);

        $folded = $headers->toFoldedString(20);

        static::assertStringNotContainsString("\t", $folded);
        static::assertStringContainsString('word1', $folded);
        static::assertStringContainsString('word2', $folded);
        static::assertStringContainsString('word3', $folded);
        static::assertStringContainsString('word4', $folded);
    }

    public function testToFoldedStringHardLimitForcesChunking(): void
    {
        $token = Str\repeat('x', 50);
        $headers = Headers::fromPairs([
            ['X', 'short ' . $token],
        ]);

        $folded = $headers->toFoldedString(20, 30);
        $lines = Byte\split($folded, "\r\n");

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(30, Byte\length($line));
        }
    }

    public function testFoldedStringSoftLimitGreaterThanHardLimitThrows(): void
    {
        $headers = Headers::fromPairs([['X-Test', 'value']]);

        $this->expectException(\Psl\MIME\Exception\InvalidArgumentException::class);

        $headers->toFoldedString(100, 50);
    }

    public function testFoldedStringZeroSoftLimitThrows(): void
    {
        $headers = Headers::fromPairs([['X-Test', 'value']]);

        $this->expectException(\Psl\MIME\Exception\InvalidArgumentException::class);

        $headers->toFoldedString(0, 998);
    }

    public function testFoldedStringZeroHardLimitThrows(): void
    {
        $headers = Headers::fromPairs([['X-Test', 'value']]);

        $this->expectException(\Psl\MIME\Exception\InvalidArgumentException::class);

        $headers->toFoldedString(78, 0);
    }
}
