<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Regex;

use PHPUnit\Framework\TestCase;
use Psl\Regex;
use Psl\Type\TypeInterface;

use function Psl\Regex\capture_groups;

final class EveryMatchTest extends TestCase
{
    /**
     * @dataProvider provideMatchingData
     */
    public function testMatching(
        array $expected,
        string $subject,
        string $pattern,
        null|TypeInterface $shape = null,
        int $offset = 0,
    ): void {
        static::assertSame($expected, Regex\every_match($subject, $pattern, $shape, $offset));
    }

    /**
     * @dataProvider provideNonMatchingData
     */
    public function testNotMatching(string $subject, string $pattern, int $offset = 0)
    {
        static::assertNull(Regex\every_match($subject, $pattern, null, $offset));
    }

    public function testMatchingWithInvalidPattern(): void
    {
        $this->expectException(Regex\Exception\InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        Regex\every_match('hello', '/hello');
    }

    public function testInvalidCaptureGroup(): void
    {
        $this->expectException(Regex\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid capture groups');

        Regex\every_match('hello', '/(hello)/', capture_groups(['doesnotexist']));
    }

    public function provideMatchingData(): iterable
    {
        yield [
            [[
                0 => 'PHP',
                1 => 'PHP',
            ]],
            'PHP is the web scripting language of choice.',
            '/(php)/i',
            capture_groups([1]),
        ];
        yield [
            [[
                0 => 'Hello world',
                1 => 'Hello',
            ]],
            'Hello world is the web scripting language of choice.',
            '/(hello) world/i',
            capture_groups([1]),
        ];
        yield [
            [[
                0 => 'web',
                1 => 'web',
            ]],
            'PHP is the web scripting language of choice.',
            '/(\bweb\b)/i',
            capture_groups([1]),
        ];
        yield [
            [[
                0 => 'web',
                1 => 'web',
            ]],
            'PHP is the web scripting language of choice.',
            '/(\bweb\b)/i',
        ];
        yield [
            [[
                0 => 'PHP',
                'language' => 'PHP',
            ]],
            'PHP is the web scripting language of choice.',
            '/(?P<language>PHP)/',
            capture_groups(['language']),
        ];
        yield [
            [[
                0 => 'PHP',
                'language' => 'PHP',
                1 => 'PHP',
            ]],
            'PHP is the web scripting language of choice.',
            '/(?P<language>PHP)/',
        ];
        yield [
            [[
                0 => 'http://www.php.net',
                1 => 'www.php.net',
            ]],
            'http://www.php.net/index.html',
            '@^(?:http://)?([^/]+)@i',
            capture_groups([1]),
        ];
        yield [
            [
                [
                    0 => 'a: 1',
                    1 => 'a',
                    2 => '1',
                ],
                [
                    0 => 'b: 2',
                    1 => 'b',
                    2 => '2',
                ],
                [
                    0 => 'c: 3',
                    1 => 'c',
                    2 => '3',
                ],
            ],
            <<<FOO
            a: 1
            b: 2
            c: 3
            FOO,
            '@(\w+): (\d+)@i',
            capture_groups([1, 2]),
        ];
        yield [
            [
                [
                    0 => 'a: 1',
                    'name' => 'a',
                    'digit' => '1',
                ],
                [
                    0 => 'b: 2',
                    'name' => 'b',
                    'digit' => '2',
                ],
                [
                    0 => 'c: 3',
                    'name' => 'c',
                    'digit' => '3',
                ],
            ],
            <<<FOO
            a: 1
            b: 2
            c: 3
            FOO,
            '@(?P<name>\w+): (?P<digit>\d+)@i',
            capture_groups(['name', 'digit']),
        ];
    }

    public function provideNonMatchingData(): iterable
    {
        yield ['PHP is the web scripting language of choice.', '/php/'];
        yield ['PHP is the website scripting language of choice.', '/\bweb\b/i'];
        yield ['php is the web scripting language of choice.', '/PHP/'];
        yield ['hello', '/[^.]+\.[^.]+$/'];
    }
}
