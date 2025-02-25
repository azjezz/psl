<?php

declare(strict_types=1);

namespace Psl\Json;

use PHPUnit\Framework\TestCase;
use Psl\Collection\MapInterface;
use Psl\Collection\VectorInterface;
use Psl\Json;
use Psl\Type;

final class TypedTest extends TestCase
{
    public function testTyped(): void
    {
        /** @var MapInterface $actual */
        $actual = Json\typed(
            '{
            "name": "azjezz/psl",
            "type": "library",
            "description": "PHP Standard Library.",
            "keywords": ["php", "std", "stdlib", "utility", "psl"],
            "license": "MIT"
        }',
            Type\map(Type\string(), Type\union(Type\string(), Type\vector(Type\string()))),
        );

        static::assertInstanceOf(MapInterface::class, $actual);
        static::assertCount(5, $actual);
        static::assertSame('azjezz/psl', $actual->at('name'));
        static::assertSame('library', $actual->at('type'));
        static::assertSame('PHP Standard Library.', $actual->at('description'));
        static::assertSame('MIT', $actual->at('license'));
        static::assertInstanceOf(VectorInterface::class, $actual->at('keywords'));
        static::assertSame(['php', 'std', 'stdlib', 'utility', 'psl'], $actual->at('keywords')->toArray());
    }

    public function testTypedVector(): void
    {
        $actual = Json\typed('["php", "std", "stdlib", "utility", "psl"]', Type\vector(Type\string()));

        static::assertInstanceOf(VectorInterface::class, $actual);
        static::assertSame(['php', 'std', 'stdlib', 'utility', 'psl'], $actual->toArray());
    }

    public function testTypedThrowsWhenUnableToCoerce(): void
    {
        $this->expectException(Json\Exception\DecodeException::class);
        $this->expectExceptionMessage(
            'Could not coerce "string" to type "' . MapInterface::class . '<string, int>" at path "name".',
        );

        Json\typed('{
            "name": "azjezz/psl",
            "type": "library",
            "description": "PHP Standard Library.",
            "keywords": ["php", "std", "stdlib", "utility", "psl"],
            "license": "MIT"
        }', Type\map(Type\string(), Type\int()));
    }

    public function testsTypedAsserts(): void
    {
        $actual = Json\typed('{"foo": "bar"}', Type\map(Type\string(), Type\string()));

        static::assertSame(['foo' => 'bar'], $actual->toArray());
    }

    public function testTypedCoerce(): void
    {
        $actual = Json\typed('{"foo": 123}', Type\map(Type\string(), Type\string()));

        static::assertSame(['foo' => '123'], $actual->toArray());
    }
}
