<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Hash\Hmac;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Hash\Hmac;

final class HashTest extends TestCase
{
    /**
     * @dataProvider provideHashData
     */
    public function testHash(string $expected, string $data, string $algorithm, string $key): void
    {
        static::assertSame($expected, Hmac\hash($data, $algorithm, $key));
    }

    public function testHashThrowsForInvalidAlgorithm(): void
    {
        $this->expectException(InvariantViolationException::class);

        Hmac\hash('Hello', 'base64', 'real-secret');
    }

    public function testHashThrowsForEmptySharedSecret(): void
    {
        $this->expectException(InvariantViolationException::class);

        Hmac\hash('Hello', 'sha1', '');
    }

    /**
     * @return Generator<int, array{0: string, 1: string, 2: string, 3: string}, mixed, void>
     */
    public function provideHashData(): Generator
    {
        yield ['03376ee7ad7bbfceee98660439a4d8b125122a5a', 'hello world', 'sha1', 'secret'];
        yield ['78d6997b1230f38e59b6d1642dfaa3a4', 'hello world', 'md5', 'secret'];
    }
}
