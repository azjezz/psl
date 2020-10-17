<?php

declare(strict_types=1);

namespace Psl\Tests\Hash;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Hash;

final class HashTest extends TestCase
{
    /**
     * @dataProvider provideHashData
     */
    public function testHash(string $expected, string $data, string $algorithm): void
    {
        static::assertSame($expected, Hash\hash($data, $algorithm));
    }

    public function testHashThrowsForUnsupportedAlgorithm(): void
    {
        $this->expectException(InvariantViolationException::class);

        Hash\hash('Hello', 'base64');
    }

    /**
     * @psalm-return Generator<int, array{0: string, 1: string, 2: string}, mixed, void>
     */
    public function provideHashData(): Generator
    {
        yield ['2aae6c35c94fcfb415dbe95f408b9ce91ee846ed', 'hello world', 'sha1'];
        yield ['5eb63bbbe01eeed093cb22bb8f5acdc3', 'hello world', 'md5'];
    }
}
