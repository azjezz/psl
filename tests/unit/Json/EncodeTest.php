<?php

declare(strict_types=1);

namespace Psl\Json;

use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psl\Math;
use Psl\Str;

use const PHP_EOL;

final class EncodeTest extends TestCase
{
    public function testEncode(): void
    {
        $actual = Json\encode(['a']);

        static::assertSame('["a"]', $actual);
    }

    public function testPrettyEncode(): void
    {
        $actual = Json\encode(
            [
                'name' => 'azjezz/psl',
                'type' => 'library',
                'description' => 'PHP Standard Library.',
                'keywords' => ['php', 'std', 'stdlib', 'utility', 'psl'],
                'license' => 'MIT',
            ],
            true,
        );

        $json = Str\replace(<<<JSON
            {
                "name": "azjezz/psl",
                "type": "library",
                "description": "PHP Standard Library.",
                "keywords": [
                    "php",
                    "std",
                    "stdlib",
                    "utility",
                    "psl"
                ],
                "license": "MIT"
            }
            JSON, PHP_EOL, "\n");

        static::assertSame($json, $actual);
    }

    public function testEncodeThrowsForMalformedUTF8(): void
    {
        $this->expectException(Json\Exception\EncodeException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded.');

        Json\encode(["bad utf\xFF"]);
    }

    public function testEncodeThrowsWithNAN(): void
    {
        $this->expectException(Json\Exception\EncodeException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded.');

        Json\encode(Math\NAN);
    }

    public function testEncodeThrowsWithInf(): void
    {
        $this->expectException(Json\Exception\EncodeException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded.');

        Json\encode(Math\INFINITY);
    }

    public function testEncodePreserveZeroFraction(): void
    {
        static::assertSame('1.0', Json\encode(1.0));
    }
}
