<?php

declare(strict_types=1);

namespace Psl\Tests\Regex;

use PHPUnit\Framework\TestCase;
use Psl\Regex;

final class MatchesTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMatches(bool $expected, string $subject, string $pattern, int $offset = 0): void
    {
        static::assertSame($expected, Regex\matches($subject, $pattern, $offset));
    }

    public function provideData(): iterable
    {
        yield [true, 'PHP is the web scripting language of choice.', '/php/i'];
        yield [true, 'PHP is the web scripting language of choice.', '/\bweb\b/i'];
        yield [true, 'PHP is the web scripting language of choice.', '/PHP/'];
        yield [true, 'PHP is the web scripting language of choice.', '/\bweb\b/'];
        yield [true, 'http://www.php.net/index.html', '@^(?:http://)?([^/]+)@i'];
        yield [true, 'www.php.net', '/[^.]+\.[^.]+$/'];

        yield [false, 'PHP is the web scripting language of choice.', '/php/'];
        yield [false, 'PHP is the website scripting language of choice.', '/\bweb\b/i'];
        yield [false, 'php is the web scripting language of choice.', '/PHP/'];
        yield [false, 'hello', '/[^.]+\.[^.]+$/'];
    }

    public function testMatchesWithInvalidPattern(): void
    {
        $this->expectException(Regex\Exception\InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        Regex\matches('hello', '/hello');
    }
}
