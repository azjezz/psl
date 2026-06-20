<?php

declare(strict_types=1);

namespace Psl\Regex\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Regex;
use Psl\Type\TypeInterface;

final class FirstMatchTest extends TestCase
{
    /**
     * @param non-empty-string $pattern
     * @param TypeInterface<array<array-key, mixed>|null>|null $shape
     */
    #[DataProvider('provideMatchingData')]
    public function testMatching(
        array $expected,
        string $subject,
        string $pattern,
        null|TypeInterface $shape = null,
        int $offset = 0,
    ): void {
        static::assertSame($expected, Regex\first_match::<array>($subject, $pattern, $shape, $offset));
    }

    /**
     * @param non-empty-string $pattern
     */
    #[DataProvider('provideNonMatchingData')]
    public function testNotMatching(string $subject, string $pattern, int $offset = 0): void
    {
        static::assertNull(Regex\first_match::<array>($subject, $pattern, null, $offset));
    }

    public function testMatchingWithInvalidPattern(): void
    {
        $this->expectException(Regex\Exception\InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        Regex\first_match::<array>('hello', '/hello');
    }

    public function testInvalidCaptureGroup(): void
    {
        $this->expectException(Regex\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid capture groups');

        Regex\first_match::<array>('hello', '/(hello)/', Regex\capture_groups(['doesnotexist']));
    }

    public static function provideMatchingData(): iterable
    {
        yield [
            [
                0 => 'PHP',
                1 => 'PHP',
            ],
            'PHP is the web scripting language of choice.',
            '/(php)/i',
            Regex\capture_groups([1]),
        ];
        yield [
            [
                0 => 'Hello world',
                1 => 'Hello',
            ],
            'Hello world is the web scripting language of choice.',
            '/(hello) world/i',
            Regex\capture_groups([1]),
        ];
        yield [
            [
                0 => 'web',
                1 => 'web',
            ],
            'PHP is the web scripting language of choice.',
            '/(\bweb\b)/i',
            Regex\capture_groups([1]),
        ];
        yield [
            [
                0 => 'web',
                1 => 'web',
            ],
            'PHP is the web scripting language of choice.',
            '/(\bweb\b)/i',
        ];
        yield [
            [
                0 => 'PHP',
                'language' => 'PHP',
            ],
            'PHP is the web scripting language of choice.',
            '/(?P<language>PHP)/',
            Regex\capture_groups(['language']),
        ];
        yield [
            [
                0 => 'http://www.php.net',
                1 => 'www.php.net',
            ],
            'http://www.php.net/index.html',
            '@^(?:http://)?([^/]+)@i',
            Regex\capture_groups([1]),
        ];
        yield [
            [
                0 => 'PHP',
                'language' => 'PHP',
                1 => 'PHP',
            ],
            'PHP is the web scripting language of choice.',
            '/(?P<language>PHP)/',
        ];
    }

    public static function provideNonMatchingData(): iterable
    {
        yield ['PHP is the web scripting language of choice.', '/php/'];
        yield ['PHP is the website scripting language of choice.', '/\bweb\b/i'];
        yield ['php is the web scripting language of choice.', '/PHP/'];
        yield ['hello', '/[^.]+\.[^.]+$/'];
    }
}
