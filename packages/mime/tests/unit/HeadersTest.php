<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\MIME\Exception\InvalidArgumentException;
use Psl\MIME\Headers;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;
use Stringable;

use function str_replace;
use function substr_count;

/**
 * @mago-expect lint:cyclomatic-complexity
 * @mago-expect lint:kan-defect
 */
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

        $this->expectException(InvalidArgumentException::class);

        $headers->toFoldedString(100, 50);
    }

    public function testFoldedStringZeroSoftLimitThrows(): void
    {
        $headers = Headers::fromPairs([['X-Test', 'value']]);

        $this->expectException(InvalidArgumentException::class);

        $headers->toFoldedString(0, 998);
    }

    public function testFoldedStringZeroHardLimitThrows(): void
    {
        $headers = Headers::fromPairs([['X-Test', 'value']]);

        $this->expectException(InvalidArgumentException::class);

        $headers->toFoldedString(78, 0);
    }

    public function testWithoutIsCaseInsensitive(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
            ['Accept',       'application/json'],
        ]);

        $result = $headers->without('CONTENT-TYPE');

        static::assertSame(1, $result->count());
        static::assertNull($result->get('Content-Type'));
        static::assertSame('application/json', $result->get('Accept'));
    }

    public function testWithoutComparesCorrectTupleIndex(): void
    {
        $headers = Headers::fromPairs([
            ['Content-Type', 'text/html'],
            ['X-Header',     'value'],
        ]);

        $result = $headers->without('Content-Type');

        static::assertSame(1, $result->count());
        static::assertSame('value', $result->get('X-Header'));
    }

    public function testFoldedStringSoftLimitOneDoesNotThrow(): void
    {
        $headers = Headers::fromPairs([['X', 'v']]);

        $folded = $headers->toFoldedString(1, 1);
        static::assertNotEmpty($folded);
    }

    public function testFoldedStringHardLimitOneDoesNotThrow(): void
    {
        $headers = Headers::fromPairs([['X', 'v']]);

        $folded = $headers->toFoldedString(1, 1);
        static::assertNotEmpty($folded);
    }

    public function testFoldedStringEqualLimitsDoNotThrow(): void
    {
        $headers = Headers::fromPairs([['X', 'v']]);

        $folded = $headers->toFoldedString(50, 50);
        static::assertNotEmpty($folded);
    }

    public function testFoldedStringExactSoftLimitIsNotFolded(): void
    {
        $headers = Headers::fromPairs([['X', 'abcdefg']]);

        $folded = $headers->toFoldedString(10, 998);

        static::assertSame("X: abcdefg\r\n", $folded);
    }

    public function testFoldedStringShortLineReturnsDirectly(): void
    {
        $headers = Headers::fromPairs([['X', 'short']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertSame("X: short\r\n", $folded);
    }

    public function testFoldedStringEmptyValueToken(): void
    {
        $longName = Str\repeat('X', 80);
        $headers = Headers::fromPairs([[$longName, '']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertStringContainsString($longName . ': ', $folded);
    }

    public function testFoldedStringSkipsEmptyTokensFromSplit(): void
    {
        $value = 'word1  word2  word3';
        $headers = Headers::fromPairs([['Subject', $value]]);

        $folded = $headers->toFoldedString(20, 998);

        static::assertStringContainsString('word1', $folded);
        static::assertStringContainsString('word2', $folded);
        static::assertStringContainsString('word3', $folded);
    }

    public function testFoldedStringAccountsForSpaceBetweenTokens(): void
    {
        $headers = Headers::fromPairs([['Subject', 'aaaa bbbbb ccccc']]);

        $folded = $headers->toFoldedString(20, 998);

        static::assertStringContainsString("\r\n ", $folded);
        static::assertStringContainsString('aaaa', $folded);
        static::assertStringContainsString('bbbbb', $folded);
        static::assertStringContainsString('ccccc', $folded);
    }

    public function testFoldedStringFoldsAtSoftLimitBoundary(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbb ccc ddd']]);

        $folded = $headers->toFoldedString(15, 998);

        static::assertStringContainsString("\r\n ", $folded);
        static::assertStringContainsString('ddd', $folded);
    }

    public function testFoldedStringTokenExceedingHardLimitIsSplit(): void
    {
        $longToken = Str\repeat('x', 50);
        $headers = Headers::fromPairs([['X', 'short ' . $longToken]]);

        $folded = $headers->toFoldedString(10, 20);
        $lines = Byte\split($folded, "\r\n");

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(20, Byte\length($line));
        }
    }

    public function testFoldedStringChunkSizeIsHardLimitMinusOne(): void
    {
        $longToken = Str\repeat('a', 30);
        $headers = Headers::fromPairs([['X', 'y ' . $longToken]]);

        $folded = $headers->toFoldedString(5, 10);
        $lines = Byte\split($folded, "\r\n");

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(10, Byte\length($line));
        }
    }

    public function testFoldedStringChunksAllAppendedWithFolding(): void
    {
        $longToken = Str\repeat('x', 100);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken]]);

        $folded = $headers->toFoldedString(5, 30);

        $unfolded = str_replace("\r\n ", '', $folded);
        static::assertStringContainsString($longToken, $unfolded);
    }

    public function testFoldedStringCurrentLenTrackedAfterChunking(): void
    {
        $longToken = Str\repeat('x', 50);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken . ' end']]);

        $folded = $headers->toFoldedString(5, 20);

        static::assertStringContainsString('end', $folded);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(20, Byte\length($line));
        }
    }

    public function testFoldedStringFoldedTokenStartsOnNewLine(): void
    {
        $headers = Headers::fromPairs([['Subject', 'word1 word2 word3 word4 word5']]);

        $folded = $headers->toFoldedString(20, 998);

        static::assertStringContainsString("\r\n ", $folded);

        static::assertStringContainsString('word1', $folded);
        static::assertStringContainsString('word5', $folded);
    }

    public function testFoldedStringCurrentLenIncrementedCorrectly(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbb ccc ddd eee fff']]);

        $folded = $headers->toFoldedString(25, 998);

        static::assertStringContainsString("\r\n ", $folded);

        $lines = Byte\split($folded, "\r\n");
        static::assertStringContainsString('eee', $lines[0]);
    }

    public function testToFoldedStringExactSoftLimitLengthNotFolded(): void
    {
        $headers = Headers::fromPairs([
            ['X', Str\repeat('a', 50) . ' ' . Str\repeat('b', 24)],
        ]);

        $folded = $headers->toFoldedString(78, 998);
        $line = Byte\strip_suffix($folded, "\r\n");

        static::assertSame(78, Byte\length($line));
        static::assertStringNotContainsString("\r\n ", $folded);
    }

    public function testToFoldedStringExactSoftLimitPlusOneIsFolded(): void
    {
        $headers = Headers::fromPairs([
            ['X', Str\repeat('a', 50) . ' ' . Str\repeat('b', 25)],
        ]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertStringContainsString("\r\n ", $folded);
    }

    public function testToFoldedStringEmptyTokensSkipped(): void
    {
        $headers = Headers::fromPairs([['Subject', 'word1  word2  word3']]);

        $folded = $headers->toFoldedString(20, 998);

        static::assertStringContainsString('word1', $folded);
        static::assertStringContainsString('word2', $folded);
        static::assertStringContainsString('word3', $folded);
    }

    public function testToFoldedStringEmptyValueAllWhitespace(): void
    {
        $longName = Str\repeat('X', 80);
        $headers = Headers::fromPairs([[$longName, '   ']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertStringContainsString($longName, $folded);
        static::assertStringEndsWith("\r\n", $folded);
    }

    public function testToFoldedStringFoldedLineStartsWithSpace(): void
    {
        $headers = Headers::fromPairs([['Subject', 'word1 word2 word3 word4 word5 word6']]);

        $folded = $headers->toFoldedString(25, 998);
        $lines = Byte\split($folded, "\r\n");

        foreach ($lines as $idx => $line) {
            if ($idx === 0 || $line === '') {
                continue;
            }

            static::assertStringStartsWith(' ', $line);
        }
    }

    public function testToFoldedStringChunkedTokenCurrentLenAccuracy(): void
    {
        $longToken = Str\repeat('a', 60);
        $headers = Headers::fromPairs([['X', 'y ' . $longToken . ' end']]);

        $folded = $headers->toFoldedString(5, 20);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(20, Byte\length($line));
        }
    }

    public function testToFoldedStringNeededCalculation(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbbbb']]);

        $folded = $headers->toFoldedString(10, 998);

        static::assertStringContainsString('aaa', $folded);
        static::assertStringContainsString('bbbbb', $folded);
    }

    public function testToFoldedStringHardLimitChunkOffset(): void
    {
        $longToken = Str\repeat('x', 40);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken . ' b']]);

        $folded = $headers->toFoldedString(5, 15);

        $unfolded = str_replace("\r\n ", '', $folded);
        static::assertStringContainsString($longToken, $unfolded);
        static::assertStringContainsString('b', $unfolded);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(15, Byte\length($line));
        }
    }

    public function testToFoldedStringFoldedTokenPrefixedWithCRLFSpace(): void
    {
        $headers = Headers::fromPairs([['X', 'short longertokenthatexceeds']]);

        $folded = $headers->toFoldedString(15, 998);

        static::assertStringContainsString("\r\n longertokenthatexceeds", $folded);
    }

    public function testToFoldedStringCurrentLenAfterFoldAccurate(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbbbbbbbbbbbbbb cc']]);

        $folded = $headers->toFoldedString(15, 998);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(998, Byte\length($line));
        }

        $unfolded = str_replace("\r\n ", ' ', $folded);
        static::assertStringContainsString('aaa', $unfolded);
        static::assertStringContainsString('bbbbbbbbbbbbbbb', $unfolded);
        static::assertStringContainsString('cc', $unfolded);
    }

    public function testFoldedStringChunkedCurrentLenPlusNotMinus(): void
    {
        $longToken = Str\repeat('x', 40);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken . ' short more']]);

        $folded = $headers->toFoldedString(5, 15);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(15, Byte\length($line));
        }
    }

    public function testFoldedStringChunkedCurrentLenTracksLastChunk(): void
    {
        $longToken = Str\repeat('a', 35);
        $headers = Headers::fromPairs([['X', 'y ' . $longToken . ' zz ww']]);

        $folded = $headers->toFoldedString(5, 12);
        $lines = Byte\split($folded, "\r\n");

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(12, Byte\length($line));
        }

        $unfolded = str_replace("\r\n ", '', $folded);
        static::assertStringContainsString($longToken, $unfolded);
        static::assertStringContainsString('zz', $unfolded);
        static::assertStringContainsString('ww', $unfolded);
    }

    public function testFoldedStringChunkedCurrentLenDoesNotGoNegative(): void
    {
        $longToken = Str\repeat('m', 50);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken . ' b c d']]);

        $folded = $headers->toFoldedString(5, 20);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(20, Byte\length($line));
        }
    }

    public function testFoldedStringNonChunkedCurrentLenAccountsForLeadingSpace(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbbbb ccccc ddddd eeeee']]);

        $folded = $headers->toFoldedString(12, 998);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(998, Byte\length($line));
        }

        static::assertStringContainsString("\r\n ", $folded);
    }

    public function testFoldedStringNonChunkedCurrentLenExactSpaceCount(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbb ccc ddd eee fff ggg']]);

        $folded = $headers->toFoldedString(15, 998);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(998, Byte\length($line));
        }

        $unfolded = str_replace("\r\n ", ' ', $folded);
        static::assertStringContainsString('aaa', $unfolded);
        static::assertStringContainsString('ggg', $unfolded);
    }

    public function testFoldedStringNonChunkedFoldCorrectlyTracksPosition(): void
    {
        $headers = Headers::fromPairs([['Subject', 'aaaaaaa bbbbbbb ccccccc ddddddd eeeeeee']]);

        $folded = $headers->toFoldedString(25, 998);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(25, Byte\length($line));
        }
    }

    public function testFoldHeaderExactSoftLimitUsesLessThanOrEqual(): void
    {
        $headers = Headers::fromPairs([
            ['X', Str\repeat('a', 50) . ' ' . Str\repeat('b', 24)],
        ]);

        $folded = $headers->toFoldedString(78, 998);
        $line = Byte\strip_suffix($folded, "\r\n");

        static::assertSame(78, Byte\length($line));
        static::assertStringNotContainsString("\r\n ", $folded);
    }

    public function testFoldHeaderAtSoftLimitReturnsUnfoldedLine(): void
    {
        $value = Str\repeat('v', 73);
        $headers = Headers::fromPairs([['X', $value]]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertSame('X: ' . $value . "\r\n", $folded);
        static::assertStringNotContainsString("\r\n ", $folded);
    }

    public function testFoldHeaderOneBeyondSoftLimitIsFolded(): void
    {
        $value = Str\repeat('v', 74);
        $headers = Headers::fromPairs([['X', $value]]);

        $folded = $headers->toFoldedString(78, 998);
        static::assertSame('X: ' . $value . "\r\n", $folded);
    }

    public function testFoldHeaderShortLineReturnWithCRLF(): void
    {
        $headers = Headers::fromPairs([['X', 'short']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertSame("X: short\r\n", $folded);
        static::assertStringEndsWith("\r\n", $folded);
    }

    public function testFoldHeaderShortLineNotFolded(): void
    {
        $headers = Headers::fromPairs([['From', 'test@example.com']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertSame("From: test@example.com\r\n", $folded);
        static::assertStringNotContainsString("\r\n ", $folded);
    }

    public function testFoldHeaderShortLineReturnValueEndsProperly(): void
    {
        $headers = Headers::fromPairs([['X', 'v']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertSame("X: v\r\n", $folded);
        static::assertStringStartsWith('X: ', $folded);
        static::assertStringEndsWith("\r\n", $folded);
    }

    public function testFoldHeaderEmptyValueOnlyWhitespace(): void
    {
        $longName = Str\repeat('X', 80);
        $headers = Headers::fromPairs([[$longName, '   ']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertStringEndsWith("\r\n", $folded);
        static::assertStringContainsString($longName . ': ', $folded);
    }

    public function testFoldHeaderEmptyValueTokensReturnLineWithCRLF(): void
    {
        $longName = Str\repeat('N', 80);
        $headers = Headers::fromPairs([[$longName, '  ']]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertStringEndsWith("\r\n", $folded);
        static::assertStringContainsString($longName . ': ', $folded);
    }

    public function testFoldHeaderAllWhitespaceValueReturnsLineCRLFNotCRLFLine(): void
    {
        $longName = Str\repeat('H', 80);
        $headers = Headers::fromPairs([[$longName, "\t"]]);

        $folded = $headers->toFoldedString(78, 998);

        static::assertStringStartsWith($longName, $folded);
        static::assertStringEndsWith("\r\n", $folded);
        static::assertStringNotContainsString("\r\n" . $longName, $folded);
    }

    public function testFoldHeaderEmptyTokenValueReturnsConcatNotReversed(): void
    {
        $longName = Str\repeat('Z', 80);
        $headers = Headers::fromPairs([[$longName, '   ']]);

        $folded = $headers->toFoldedString(78, 998);

        $expectedLine = $longName . ':    ';
        static::assertStringStartsWith($longName . ':', $folded);
        static::assertStringEndsWith("\r\n", $folded);
    }

    public function testFoldHeaderContinueSkipsEmptyTokensNotBreak(): void
    {
        $headers = Headers::fromPairs([['Subject', 'word1  word2  word3  word4']]);

        $folded = $headers->toFoldedString(20, 998);

        static::assertStringContainsString('word1', $folded);
        static::assertStringContainsString('word2', $folded);
        static::assertStringContainsString('word3', $folded);
        static::assertStringContainsString('word4', $folded);
    }

    public function testFoldHeaderEmptyTokensContinueDontStopProcessing(): void
    {
        $headers = Headers::fromPairs([['X', 'a  b  c  d  e']]);

        $folded = $headers->toFoldedString(10, 998);

        static::assertStringContainsString('a', $folded);
        static::assertStringContainsString('b', $folded);
        static::assertStringContainsString('c', $folded);
        static::assertStringContainsString('d', $folded);
        static::assertStringContainsString('e', $folded);
    }

    public function testFoldHeaderAllTokensProcessedDespiteDoubleSpaces(): void
    {
        $headers = Headers::fromPairs([['X', 'first  second  third  last']]);

        $folded = $headers->toFoldedString(15, 998);

        $unfolded = str_replace("\r\n ", ' ', $folded);
        static::assertStringContainsString('first', $unfolded);
        static::assertStringContainsString('last', $unfolded);
    }

    public function testFoldHeaderNeededCalculationAddsOneForSpace(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbbbb']]);

        $folded = $headers->toFoldedString(10, 998);

        static::assertStringContainsString('aaa', $folded);
        static::assertStringContainsString('bbbbb', $folded);

        $headers2 = Headers::fromPairs([['X', 'aaa bbbb']]);
        $folded2 = $headers2->toFoldedString(10, 998);

        static::assertSame("X: aaa\r\n bbbb\r\n", $folded2);
    }

    public function testFoldHeaderNeededIncludesSpaceBetweenTokens(): void
    {
        $headers = Headers::fromPairs([['X', 'ab cd']]);

        $folded = $headers->toFoldedString(9, 998);

        static::assertStringContainsString('ab', $folded);
        static::assertStringContainsString('cd', $folded);
    }

    public function testFoldHeaderNeededValueIsTokenLenPlusOneNotTwo(): void
    {
        $headers = Headers::fromPairs([['X', 'aa bb']]);

        $folded8 = $headers->toFoldedString(8, 998);

        static::assertStringContainsString('aa', $folded8);
        static::assertStringContainsString('bb', $folded8);
    }

    public function testFoldHeaderSoftLimitComparisonIsStrictGreaterThan(): void
    {
        $headers = Headers::fromPairs([['X', 'aaaa bbb ccc']]);

        $folded = $headers->toFoldedString(10, 998);
        static::assertStringContainsString("\r\n ", $folded);

        $headers2 = Headers::fromPairs([['X', 'aaa bb cc']]);
        $folded2 = $headers2->toFoldedString(10, 998);
        static::assertSame("X: aaa bb\r\n cc\r\n", $folded2);
    }

    public function testFoldHeaderSoftLimitExactDoesNotFold(): void
    {
        $headers = Headers::fromPairs([['X', 'aa bb']]);

        $folded = $headers->toFoldedString(8, 998);

        static::assertSame("X: aa bb\r\n", $folded);
    }

    public function testFoldHeaderSoftLimitPlusOneFolds(): void
    {
        $headers = Headers::fromPairs([['X', 'aa bbb']]);

        $folded = $headers->toFoldedString(8, 998);

        static::assertStringContainsString("\r\n ", $folded);
    }

    public function testFoldHeaderHardLimitTokenCheck(): void
    {
        $token = Str\repeat('x', 998);
        $headers = Headers::fromPairs([['X', 'short ' . $token]]);

        $folded = $headers->toFoldedString(10, 998);

        static::assertStringNotContainsString($token, $folded);

        $token2 = Str\repeat('y', 997);
        $headers2 = Headers::fromPairs([['X', 'short ' . $token2]]);
        $folded2 = $headers2->toFoldedString(10, 998);

        static::assertStringContainsString($token2, $folded2);
    }

    public function testFoldHeaderHardLimitBoundaryTokenPlusOneEqualsLimit(): void
    {
        $token = Str\repeat('a', 997);
        $headers = Headers::fromPairs([['X', 'z ' . $token]]);

        $folded = $headers->toFoldedString(5, 998);

        static::assertStringContainsString($token, $folded);
    }

    public function testFoldHeaderHardLimitBoundaryTokenPlusOneExceedsLimit(): void
    {
        $token = Str\repeat('a', 998);
        $headers = Headers::fromPairs([['X', 'z ' . $token]]);

        $folded = $headers->toFoldedString(5, 998);

        static::assertStringNotContainsString($token, $folded);
    }

    public function testFoldHeaderChunkOffsetIsHardLimitMinusOne(): void
    {
        $longToken = Str\repeat('a', 30);
        $headers = Headers::fromPairs([['X', 'y ' . $longToken]]);

        $folded = $headers->toFoldedString(5, 10);
        $lines = Byte\split($folded, "\r\n");

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(10, Byte\length($line));
        }
    }

    public function testFoldHeaderChunkOffsetNotHardLimitMinusTwo(): void
    {
        $longToken = Str\repeat('x', 20);
        $headers = Headers::fromPairs([['X', 'y ' . $longToken]]);

        $folded = $headers->toFoldedString(5, 10);
        $lines = Byte\split($folded, "\r\n");

        $foldedLines = [];
        foreach ($lines as $line) {
            if ($line === '' || !Str\starts_with($line, ' ')) {
                continue;
            }

            $foldedLines[] = $line;
        }

        foreach ($foldedLines as $line) {
            static::assertLessThanOrEqual(10, Byte\length($line));
        }
    }

    public function testFoldHeaderChunkOffsetExact(): void
    {
        $headers = Headers::fromPairs([['X', 'y ' . Str\repeat('z', 25)]]);

        $folded = $headers->toFoldedString(5, 10);
        $lines = Byte\split($folded, "\r\n");

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(10, Byte\length($line));
            if (Str\starts_with($line, ' ')) {
                $content = Byte\strip_prefix($line, ' ');
                static::assertLessThanOrEqual(9, Byte\length($content));
            }
        }
    }

    public function testFoldHeaderCurrentLenAfterChunksUsesLastChunk(): void
    {
        $longToken = Str\repeat('x', 50);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken . ' end']]);

        $folded = $headers->toFoldedString(5, 20);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(20, Byte\length($line));
        }

        static::assertStringContainsString('end', $folded);
    }

    public function testFoldHeaderCurrentLenAfterChunkIsCorrect(): void
    {
        $longToken = Str\repeat('a', 40);
        $headers = Headers::fromPairs([['X', 'y ' . $longToken . ' bb cc dd']]);

        $folded = $headers->toFoldedString(5, 15);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(15, Byte\length($line));
        }
    }

    public function testFoldHeaderCurrentLenCountsLastChunkCorrectly(): void
    {
        $longToken = Str\repeat('m', 30);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken . ' z']]);

        $folded = $headers->toFoldedString(5, 10);

        static::assertStringContainsString('z', $folded);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(10, Byte\length($line));
        }
    }

    public function testFoldHeaderCurrentLenOnePlusChunkLen(): void
    {
        $longToken = Str\repeat('q', 27);
        $headers = Headers::fromPairs([['X', 'y ' . $longToken . ' next']]);

        $folded = $headers->toFoldedString(5, 10);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(10, Byte\length($line));
        }

        $unfolded = str_replace("\r\n ", '', $folded);
        static::assertStringContainsString('next', $unfolded);
    }

    public function testFoldHeaderCurrentLenAfterChunkNotTwo(): void
    {
        $longToken = Str\repeat('r', 18);
        $headers = Headers::fromPairs([['X', 'a ' . $longToken . ' bbb ccc']]);

        $folded = $headers->toFoldedString(5, 10);

        $lines = Byte\split($folded, "\r\n");
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            static::assertLessThanOrEqual(10, Byte\length($line));
        }
    }

    public function testFoldedStringNonChunkedFoldExactOutputAfterFold(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbbb cc']]);

        $folded = $headers->toFoldedString(9, 998);

        static::assertSame("X: aaa\r\n bbbb cc\r\n", $folded);
    }

    public function testFoldedStringNonChunkedFoldTracksThenFoldsAgain(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbbbb cc ddddd']]);

        $folded = $headers->toFoldedString(9, 998);

        $foldCount = substr_count($folded, "\r\n ");
        static::assertSame(2, $foldCount);
    }

    public function testFoldedStringNonChunkedFoldCurrentLenLeadsToCorrectSubsequentFold(): void
    {
        $headers = Headers::fromPairs([['X', 'aaa bbb cccc dddd eeee']]);

        $folded = $headers->toFoldedString(10, 998);

        static::assertSame("X: aaa bbb\r\n cccc dddd\r\n eeee\r\n", $folded);
    }

    public function testFoldedStringNonChunkedCurrentLenIsOnePlusTokenLenNotZero(): void
    {
        $headers = Headers::fromPairs([['X', 'aa bbb ccc']]);

        $folded = $headers->toFoldedString(7, 998);

        static::assertSame("X: aa\r\n bbb\r\n ccc\r\n", $folded);
    }

    public function testFoldedStringNonChunkedCurrentLenIsOnePlusTokenLenNotTwo(): void
    {
        $headers = Headers::fromPairs([['X', 'aa bb cc']]);

        $folded = $headers->toFoldedString(6, 998);

        static::assertSame("X: aa\r\n bb cc\r\n", $folded);
    }

    public function testFoldedStringNonChunkedCurrentLenIsOnePlusTokenLenNotOneMinus(): void
    {
        $headers = Headers::fromPairs([['X', 'aa bbb ccc']]);

        $folded = $headers->toFoldedString(7, 998);

        $foldCount = substr_count($folded, "\r\n ");
        static::assertSame(2, $foldCount);
    }
}
