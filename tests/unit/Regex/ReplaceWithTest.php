<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Regex;

use PHPUnit\Framework\TestCase;
use Psl\Regex;

final class ReplaceWithTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReplaceWith(
        string $expected,
        string $subject,
        string $pattern,
        callable $callback,
        null|int $limit = null,
    ): void {
        static::assertSame($expected, Regex\replace_with($subject, $pattern, $callback, $limit));
    }

    public function provideData(): iterable
    {
        yield [
            'April fools day is 04/01/2003',
            'April fools day is 04/01/2002',
            '|(\d{2}/\d{2}/)(\d{4})|',
            static fn(array $matches): string => $matches[1] . ((int) $matches[2]) + 1,
            null,
        ];

        yield [
            'Last christmas was 12/24/2021',
            'Last christmas was 12/24/2001',
            '|(\d{2}/\d{2}/)(\d{4})|',
            static fn(array $matches): string => $matches[1] . ((int) $matches[2]) + 20,
            null,
        ];

        yield [
            'Last christmas was 12/24/2021, April fools day is 04/01/2022',
            'Last christmas was 12/24/2001, April fools day is 04/01/2002',
            '|(\d{2}/\d{2}/)(\d{4})|',
            static fn(array $matches): string => $matches[1] . ((int) $matches[2]) + 20,
            null,
        ];

        yield [
            'Last christmas was 12/24/2021, April fools day is 04/01/2002',
            'Last christmas was 12/24/2001, April fools day is 04/01/2002',
            '|(\d{2}/\d{2}/)(\d{4})|',
            static fn(array $matches): string => $matches[1] . ((int) $matches[2]) + 20,
            1,
        ];
    }

    public function testReplaceWithInvalidPattern(): void
    {
        $this->expectException(Regex\Exception\InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '|' found");

        Regex\replace_with(
            'April 15, 2003',
            '|(\d{2}/\d{2}/)(\d{4})',
            static fn(array $matches): string => $matches[1] . ((int) $matches[2]) + 20,
        );
    }
}
