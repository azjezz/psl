<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Str;
use Psl\Type;

final class TypeAssertExceptionTest extends TestCase
{
    public function testIncorrectIterableKey(): void
    {
        $type = Type\iterable(Type\int(), Type\instance_of(Collection\CollectionInterface::class));

        try {
            $type->assert([
                'hello' => new Collection\Vector([1, 2, 3])
            ]);

            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame('int', $e->getExpectedType());
            static::assertSame('string', $e->getActualType());
            static::assertSame('Expected "int", got "string".', $e->getMessage());

            $trace  = $e->getTypeTrace();
            $frames = $trace->getFrames();

            static::assertCount(1, $frames);
            static::assertSame('iterable<int, _>', Iter\first($frames));
        }
    }

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

            $trace  = $e->getTypeTrace();
            $frames = $trace->getFrames();

            static::assertCount(0, $frames);
        }
    }
}
