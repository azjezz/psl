<?php

declare(strict_types=1);

namespace Psl\URI\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\URI\Internal\Normalizer;

final class NormalizerTest extends TestCase
{
    public function testNormalizeSchemeToLowercase(): void
    {
        static::assertSame('http', Normalizer::normalizeScheme('HTTP'));
    }

    public function testNormalizeSchemeAlreadyLowercase(): void
    {
        static::assertSame('ftp', Normalizer::normalizeScheme('ftp'));
    }

    public function testNormalizeSchemeEmptyString(): void
    {
        static::assertSame('', Normalizer::normalizeScheme(''));
    }

    public function testNormalizeSchemeMixedCase(): void
    {
        static::assertSame('coap+tcp', Normalizer::normalizeScheme('CoAp+TcP'));
    }

    public function testNormalizeHostToLowercase(): void
    {
        static::assertSame('example.com', Normalizer::normalizeHost('EXAMPLE.COM'));
    }

    public function testNormalizeHostDecodesUnreservedPercent(): void
    {
        static::assertSame('host', Normalizer::normalizeHost('%68%6F%73%74'));
    }

    public function testNormalizeEncodingDecodesUnreserved(): void
    {
        static::assertSame('A', Normalizer::normalizeEncoding('%41'));
    }

    public function testNormalizeEncodingUppercasesReservedHex(): void
    {
        static::assertSame('%2F', Normalizer::normalizeEncoding('%2f'));
    }

    public function testRemoveDotSegmentsEmptyPath(): void
    {
        static::assertSame('', Normalizer::removeDotSegments(''));
    }

    public function testRemoveDotSegmentsNoDots(): void
    {
        static::assertSame('/a/b/c', Normalizer::removeDotSegments('/a/b/c'));
    }

    public function testRemoveDotSegmentsSingleDot(): void
    {
        static::assertSame('', Normalizer::removeDotSegments('.'));
    }

    public function testRemoveDotSegmentsDoubleDot(): void
    {
        static::assertSame('', Normalizer::removeDotSegments('..'));
    }

    public function testRemoveDotSegmentsLeadingDotSlash(): void
    {
        static::assertSame('a', Normalizer::removeDotSegments('./a'));
    }

    public function testRemoveDotSegmentsLeadingDotDotSlash(): void
    {
        static::assertSame('a', Normalizer::removeDotSegments('../a'));
    }

    public function testRemoveDotSegmentsSlashDotSlash(): void
    {
        static::assertSame('/a/b', Normalizer::removeDotSegments('/a/./b'));
    }

    public function testRemoveDotSegmentsSlashDotEnd(): void
    {
        static::assertSame('/a/', Normalizer::removeDotSegments('/a/.'));
    }

    public function testRemoveDotSegmentsSlashDotDotSlash(): void
    {
        static::assertSame('/a/c', Normalizer::removeDotSegments('/a/b/../c'));
    }

    public function testRemoveDotSegmentsSlashDotDotEnd(): void
    {
        static::assertSame('/a/', Normalizer::removeDotSegments('/a/b/..'));
    }

    public function testRemoveDotSegmentsMultipleTraversals(): void
    {
        static::assertSame('/a/e', Normalizer::removeDotSegments('/a/b/c/../../d/../e'));
    }

    public function testRemoveDotSegmentsExcessiveTraversal(): void
    {
        static::assertSame('/g', Normalizer::removeDotSegments('/a/b/../../../g'));
    }

    public function testRemoveDotSegmentsLeadingMultipleDotDotSlash(): void
    {
        static::assertSame('g', Normalizer::removeDotSegments('../../../g'));
    }

    public function testRemoveDotSegmentsLeadingDotSlashFollowedBySegment(): void
    {
        static::assertSame('b/c', Normalizer::removeDotSegments('./b/c'));
    }

    public function testRemoveDotSegmentsRootlessWithDotDotSlash(): void
    {
        static::assertSame('b', Normalizer::removeDotSegments('../b'));
    }

    public function testRemoveDotSegmentsRootlessMultipleSegments(): void
    {
        static::assertSame('a/c', Normalizer::removeDotSegments('a/b/../c'));
    }

    public function testRemoveDotSegmentsRootlessSegmentThenSlash(): void
    {
        static::assertSame('a/', Normalizer::removeDotSegments('a/b/..'));
    }

    public function testRemoveDotSegmentsRootlessNoSlash(): void
    {
        static::assertSame('a', Normalizer::removeDotSegments('a'));
    }

    public function testRemoveDotSegmentsRootlessMultipleSegmentsNoTraversal(): void
    {
        static::assertSame('a/b/c', Normalizer::removeDotSegments('a/b/c'));
    }

    #[DataProvider('dotSegmentProvider')]
    public function testRemoveDotSegmentsFromRFC3986(string $input, string $expected): void
    {
        static::assertSame($expected, Normalizer::removeDotSegments($input));
    }

    public static function dotSegmentProvider(): array
    {
        return [
            'leading ../ stripped' => ['../a/b', 'a/b'],
            'leading ./ stripped' => ['./a/b', 'a/b'],
            'multiple leading ../' => ['../../a', 'a'],
            'multiple leading ./' => ['././a', 'a'],
            'mixed leading dot segments' => ['./../a', 'a'],
            '/. at end replaced with /' => ['/a/.', '/a/'],
            '/.. at end pops and replaces with /' => ['/a/b/..', '/a/'],
            '/./ in middle' => ['/a/./b/./c', '/a/b/c'],
            '/../ in middle' => ['/a/b/../c', '/a/c'],
            'only .' => ['.', ''],
            'only ..' => ['..', ''],
            'rootless with slash' => ['a/b/c', 'a/b/c'],
            'rootless single segment' => ['abc', 'abc'],
            'rootless dot-dot traversal' => ['a/b/../c/d', 'a/c/d'],
            'rootless dot traversal' => ['a/./b', 'a/b'],
            'rootless ending no slash' => ['a/b', 'a/b'],
        ];
    }
}
