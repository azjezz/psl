<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Psl\HTTP\Client\Internal\should_tunnel;

final class ShouldTunnelTest extends TestCase
{
    /**
     * @param list<non-empty-string> $noTunneling
     */
    #[DataProvider('shouldTunnelProvider')]
    public function testShouldTunnel(string $host, array $noTunneling, bool $expected): void
    {
        static::assertSame($expected, should_tunnel($host, $noTunneling));
    }

    /**
     * @return iterable<string, array{string, list<non-empty-string>, bool}>
     */
    public static function shouldTunnelProvider(): iterable
    {
        yield 'empty list always tunnels' => ['example.com', [], true];

        yield 'wildcard bypasses everything' => ['example.com', ['*'], false];

        yield 'exact match localhost' => ['localhost', ['localhost'], false];

        yield 'no match returns true' => ['example.com', ['localhost'], true];

        yield 'suffix match with dot prefix' => ['sub.example.com', ['.example.com'], false];

        yield 'suffix match without dot prefix' => ['sub.example.com', ['example.com'], false];

        yield 'exact domain match' => ['example.com', ['example.com'], false];

        yield 'not a suffix match' => ['notexample.com', ['example.com'], true];

        yield 'deep suffix match' => ['deep.sub.example.com', ['.example.com'], false];

        yield 'second rule matches' => ['example.com', ['other.com', 'example.com'], false];
    }
}
