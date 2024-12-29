<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Str;
use Psl\Type;

final class TypeAssertExceptionTest extends TestCase
{
    public function testIncorrectResourceType(): void
    {
        $type = Type\resource('curl');

        try {
            $type->assert(STDIN);

            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame('resource (curl)', $e->getExpectedType());
            static::assertSame('resource (stream)', $e->getActualType());
            static::assertSame('Expected "resource (curl)", got "resource (stream)".', $e->getMessage());
            static::assertSame(0, $e->getCode());
            static::assertSame([], $e->getPaths());
        }
    }

    public function testIncorrectNestedType()
    {
        $type = Type\shape(['child' => Type\shape(['name' => Type\string()])]);

        try {
            $type->assert(['child' => ['name' => 123]]);

            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame('array{\'child\': array{\'name\': string}}', $e->getExpectedType());
            static::assertSame('array', $e->getActualType());
            static::assertSame('int', $e->getFirstFailingActualType());
            static::assertSame(
                'Expected "array{\'child\': array{\'name\': string}}", got "int" at path "child.name".',
                $e->getMessage(),
            );
            static::assertSame(0, $e->getCode());
            static::assertSame(['child', 'name'], $e->getPaths());

            $previous = $e->getPrevious();
            static::assertInstanceOf(Type\Exception\AssertException::class, $previous);
            static::assertSame(
                'Expected "array{\'name\': string}", got "int" at path "name".',
                $previous->getMessage(),
            );
            static::assertSame('int', $previous->getActualType());
            static::assertSame('int', $previous->getFirstFailingActualType());
            static::assertSame(0, $previous->getCode());
            static::assertSame(['name'], $previous->getpaths());

            $previous = $previous->getPrevious();
            static::assertInstanceOf(Type\Exception\AssertException::class, $previous);
            static::assertSame('Expected "string", got "int".', $previous->getMessage());
            static::assertSame('int', $previous->getActualType());
            static::assertSame('int', $previous->getFirstFailingActualType());
            static::assertSame(0, $previous->getCode());
            static::assertSame([], $previous->getpaths());

            $previous = $previous->getPrevious();
            static::assertNull($previous);
        }
    }
}
