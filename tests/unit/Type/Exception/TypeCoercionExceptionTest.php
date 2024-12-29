<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Str;
use Psl\Type;

final class TypeCoercionExceptionTest extends TestCase
{
    public function testIncorrectResourceType(): void
    {
        $type = Type\resource('curl');

        try {
            $type->coerce(new Collection\Map(['hello' => 'foo']));

            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame('resource (curl)', $e->getTargetType());
            static::assertSame(Collection\Map::class, $e->getActualType());
            static::assertSame(0, $e->getCode());
            static::assertSame(
                Str\format('Could not coerce "%s" to type "resource (curl)".', Collection\Map::class),
                $e->getMessage(),
            );
            static::assertSame([], $e->getPaths());
        }
    }

    public function testIncorrectNestedType()
    {
        $type = Type\shape(['child' => Type\shape(['name' => Type\string()])]);

        try {
            $type->coerce(['child' => ['name' => new class() {
            }]]);

            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame('array{\'child\': array{\'name\': string}}', $e->getTargetType());
            static::assertSame('array', $e->getActualType());
            static::assertSame('class@anonymous', $e->getFirstFailingActualType());
            static::assertSame(
                'Could not coerce "class@anonymous" to type "array{\'child\': array{\'name\': string}}" at path "child.name".',
                $e->getMessage(),
            );
            static::assertSame(0, $e->getCode());
            static::assertSame(['child', 'name'], $e->getPaths());

            $previous = $e->getPrevious();
            static::assertInstanceOf(Type\Exception\CoercionException::class, $previous);
            static::assertSame(
                'Could not coerce "class@anonymous" to type "array{\'name\': string}" at path "name".',
                $previous->getMessage(),
            );
            static::assertSame('class@anonymous', $previous->getActualType());
            static::assertSame('class@anonymous', $previous->getFirstFailingActualType());
            static::assertSame(0, $previous->getCode());
            static::assertSame(['name'], $previous->getpaths());

            $previous = $previous->getPrevious();
            static::assertInstanceOf(Type\Exception\CoercionException::class, $previous);
            static::assertSame('Could not coerce "class@anonymous" to type "string".', $previous->getMessage());
            static::assertSame('class@anonymous', $previous->getActualType());
            static::assertSame('class@anonymous', $previous->getFirstFailingActualType());
            static::assertSame(0, $previous->getCode());
            static::assertSame([], $previous->getpaths());

            $previous = $previous->getPrevious();
            static::assertNull($previous);
        }
    }
}
