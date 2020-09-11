<?php

declare(strict_types=1);

namespace Psl\Json;

use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psl\Type;

class TypedTest extends TestCase
{
    public function testTyped(): void
    {
        $actual = Json\typed('{
            "name": "azjezz/psl",
            "type": "library",
            "description": "PHP Standard Library.",
            "keywords": ["php", "std", "stdlib", "utility", "psl"],
            "license": "MIT"
        }', Type\arr(Type\string(), Type\union(Type\string(), Type\arr(Type\int(), Type\string()))));

        self::assertSame([
            'name' => 'azjezz/psl',
            'type' => 'library',
            'description' => 'PHP Standard Library.',
            'keywords' => ['php', 'std', 'stdlib', 'utility', 'psl'],
            'license' => 'MIT'
        ], $actual);
    }

    public function testTypedThrowsWhenUnableToCoerce(): void
    {
        $this->expectException(Json\Exception\DecodeException::class);
        $this->expectExceptionMessage('Could not coerce "string" to type "int".');

        Json\typed('{
            "name": "azjezz/psl",
            "type": "library",
            "description": "PHP Standard Library.",
            "keywords": ["php", "std", "stdlib", "utility", "psl"],
            "license": "MIT"
        }', Type\arr(Type\string(), Type\int()));
    }

    public function testsTypedAsserts(): void
    {
        $actual = Json\typed('{"foo": "bar"}', Type\arr(Type\string(), Type\string()));

        self::assertSame(['foo' => 'bar'], $actual);
    }

    public function testTypedCoerce(): void
    {
        $actual = Json\typed('{"foo": 123}', Type\arr(Type\string(), Type\string()));

        self::assertSame(['foo' => '123'], $actual);
    }
}
