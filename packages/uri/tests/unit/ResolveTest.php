<?php

declare(strict_types=1);

namespace Psl\URI\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\URI;

final class ResolveTest extends TestCase
{
    #[DataProvider('normalExamplesProvider')]
    public function testNormalExamples(string $reference, string $expected): void
    {
        $base = URI\parse('http://a/b/c/d;p?q');
        $ref = URI\parse($reference);
        $resolved = URI\resolve($base, $ref);

        static::assertSame($expected, $resolved->toString());
    }

    public static function normalExamplesProvider(): array
    {
        return [
            ['g:h',     'g:h'],
            ['g',       'http://a/b/c/g'],
            ['./g',     'http://a/b/c/g'],
            ['g/',      'http://a/b/c/g/'],
            ['/g',      'http://a/g'],
            ['//g/h',   'http://g/h'],
            ['?y',      'http://a/b/c/d;p?y'],
            ['g?y',     'http://a/b/c/g?y'],
            ['#s',      'http://a/b/c/d;p?q#s'],
            ['g#s',     'http://a/b/c/g#s'],
            ['g?y#s',   'http://a/b/c/g?y#s'],
            [';x',      'http://a/b/c/;x'],
            ['g;x',     'http://a/b/c/g;x'],
            ['g;x?y#s', 'http://a/b/c/g;x?y#s'],
            ['',        'http://a/b/c/d;p?q'],
            ['.',       'http://a/b/c/'],
            ['./',      'http://a/b/c/'],
            ['..',      'http://a/b/'],
            ['../',     'http://a/b/'],
            ['../g',    'http://a/b/g'],
            ['../..',   'http://a/'],
            ['../../',  'http://a/'],
            ['../../g', 'http://a/g'],
        ];
    }

    #[DataProvider('abnormalExamplesProvider')]
    public function testAbnormalExamples(string $reference, string $expected): void
    {
        $base = URI\parse('http://a/b/c/d;p?q');
        $ref = URI\parse($reference);
        $resolved = URI\resolve($base, $ref);

        static::assertSame($expected, $resolved->toString());
    }

    public static function abnormalExamplesProvider(): array
    {
        return [
            ['../../../g',    'http://a/g'],
            ['../../../../g', 'http://a/g'],
            ['/./g',          'http://a/g'],
            ['/../g',         'http://a/g'],
            ['g.',            'http://a/b/c/g.'],
            ['.g',            'http://a/b/c/.g'],
            ['g..',           'http://a/b/c/g..'],
            ['..g',           'http://a/b/c/..g'],
        ];
    }

    public function testEmptyReferenceResolvesToBase(): void
    {
        $base = URI\parse('http://a/b/c/d;p?q');
        $ref = URI\parse('');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://a/b/c/d;p?q', $resolved->toString());
    }

    public function testSameDocumentFragmentReference(): void
    {
        $base = URI\parse('http://example.com/path?query');
        $ref = URI\parse('#frag');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://example.com/path?query#frag', $resolved->toString());
    }

    public function testQueryOnlyReference(): void
    {
        $base = URI\parse('http://example.com/path?old');
        $ref = URI\parse('?new');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://example.com/path?new', $resolved->toString());
    }

    public function testSchemeOnlyMatch(): void
    {
        $base = URI\parse('http://a/b/c');
        $ref = URI\parse('ftp://other/path');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('ftp://other/path', $resolved->toString());
    }

    public function testEmptyFragmentReference(): void
    {
        $base = URI\parse('http://example.com/path?query#oldfrag');
        $ref = URI\parse('#');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://example.com/path?query#', $resolved->toString());
    }

    public function testAuthorityOnlyReference(): void
    {
        $base = URI\parse('http://a/b/c');
        $ref = URI\parse('//newhost/newpath');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://newhost/newpath', $resolved->toString());
    }

    public function testResolveWithBaseFragment(): void
    {
        $base = URI\parse('http://a/b/c#basefrag');
        $ref = URI\parse('d');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://a/b/d', $resolved->toString());
    }

    public function testResolveEmptyQueryReference(): void
    {
        $base = URI\parse('http://a/b/c?old');
        $ref = URI\parse('?');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://a/b/c?', $resolved->toString());
    }

    public function testResolveAbsolutePathReference(): void
    {
        $base = URI\parse('http://a/b/c/d');
        $ref = URI\parse('/e/f');
        $resolved = URI\resolve($base, $ref);

        static::assertSame('http://a/e/f', $resolved->toString());
    }
}
