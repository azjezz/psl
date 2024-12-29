<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Regex;

use PHPUnit\Framework\TestCase;
use Psl\Regex;

final class ReplaceEveryTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReplaceEvery(string $expected, string $subject, array $replacements): void
    {
        static::assertSame($expected, Regex\replace_every($subject, $replacements));
    }

    public function provideData(): iterable
    {
        yield [
            'April1,2003',
            'April 15, 2003',
            [
                '/(\w+) (\d+), (\d+)/i' => '${1}1,$3',
            ],
        ];

        yield [
            'The slow black bear jumps over the lazy dog.',
            'The quick brown fox jumps over the lazy dog.',
            [
                '/quick/' => 'slow',
                '/brown/' => 'black',
                '/fox/' => 'bear',
            ],
        ];

        yield [
            'Hello, World!',
            'Hello, World!',
            [
                '/foo/' => 'bar',
            ],
        ];
    }

    public function testReplaceEveryWithInvalidPattern(): void
    {
        $this->expectException(Regex\Exception\InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        Regex\replace_every('April 15, 2003', ['/(\w+) (\d+), (\d+)' => '${1}1,$3']);
    }
}
