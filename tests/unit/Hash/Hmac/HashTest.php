<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Hash\Hmac;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Hash\Hmac;

final class HashTest extends TestCase
{
    /**
     * @param non-empty-string $expected
     * @param non-empty-string $data
     * @param non-empty-string $key
     *
     * @dataProvider provideHashData
     */
    public function testHash(string $expected, string $data, Hmac\Algorithm $algorithm, string $key): void
    {
        static::assertSame($expected, Hmac\hash($data, $algorithm, $key));
    }

    /**
     * @return Generator<int, array{0: non-empty-string, 1: non-empty-string, 2: Hmac\Algorithm, 3: non-empty-string}, mixed, void>
     */
    public function provideHashData(): Generator
    {
        yield ['03376ee7ad7bbfceee98660439a4d8b125122a5a', 'hello world', Hmac\Algorithm::SHA1, 'secret'];
        yield ['78d6997b1230f38e59b6d1642dfaa3a4', 'hello world', Hmac\Algorithm::MD5, 'secret'];
    }
}
