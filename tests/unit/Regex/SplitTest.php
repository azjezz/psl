<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Regex;

use PHPUnit\Framework\TestCase;
use Psl\Regex;

final class SplitTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSplit(array $expected, string $subject, string $pattern, null|int $limit = null): void
    {
        static::assertSame($expected, Regex\split($subject, $pattern, $limit));
    }

    public function provideData(): iterable
    {
        yield [
            ['hello'],
            'hello',
            "/[\s,]+/",
        ];

        yield [
            ['php', 'standard', 'library'],
            'php standard library',
            "/[\s,]+/",
        ];

        yield [
            ['p', 'h', 'p', ' ', 's', 't', 'a', 'n', 'd', 'a', 'r', 'd', ' ', 'l', 'i', 'b', 'r', 'a', 'r', 'y'],
            'php standard library',
            '//',
        ];

        yield [
            ['p', 'h', 'p', ' ', 'standard library'],
            'php standard library',
            '//',
            5,
        ];
    }

    public function testReplaceWithInvalidPattern(): void
    {
        $this->expectException(Regex\Exception\InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        Regex\split('php standard library', '/');
    }
}
