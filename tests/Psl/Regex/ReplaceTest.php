<?php

declare(strict_types=1);

namespace Psl\Tests\Regex;

use PHPUnit\Framework\TestCase;
use Psl\Regex;

final class ReplaceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReplace(string $expected, string $subject, string $pattern, string $replacement): void
    {
        static::assertSame($expected, Regex\replace($subject, $pattern, $replacement));
    }

    public function provideData(): iterable
    {
        yield ['April1,2003', 'April 15, 2003', '/(\w+) (\d+), (\d+)/i', '${1}1,$3'];

        yield ['Hello, World!', 'Hello, World!', '/foo/', 'bar'];
    }

    public function testReplaceWithInvalidPattern(): void
    {
        $this->expectException(Regex\Exception\InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        Regex\replace('April 15, 2003', '/(\w+) (\d+), (\d+)', '${1}1,$3');
    }
}
