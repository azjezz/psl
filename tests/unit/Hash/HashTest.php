<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Hash;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Hash;

final class HashTest extends TestCase
{
    /**
     * @param non-empty-string $expected
     * @param non-empty-string $data
     *
     * @dataProvider provideHashData
     */
    public function testHash(string $expected, string $data, Hash\Algorithm $algorithm): void
    {
        static::assertSame($expected, Hash\hash($data, $algorithm));
    }

    /**
     * @return Generator<int, array{0: non-empty-string, 1: non-empty-string, 2: Hash\Algorithm}, mixed, void>
     */
    public function provideHashData(): Generator
    {
        yield ['2aae6c35c94fcfb415dbe95f408b9ce91ee846ed', 'hello world', Hash\Algorithm::SHA1];
        yield ['5eb63bbbe01eeed093cb22bb8f5acdc3', 'hello world', Hash\Algorithm::MD5];
    }
}
