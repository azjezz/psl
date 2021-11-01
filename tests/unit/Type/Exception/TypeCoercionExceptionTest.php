<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Str;
use Psl\Type;

final class TypeCoercionExceptionTest extends TestCase
{
    public function testIncorrectIterableKey(): void
    {
        $type = Type\iterable(Type\bool(), Type\instance_of(Collection\CollectionInterface::class));

        try {
            $type->coerce([
                4 => new Collection\Vector([1, 2, 3])
            ]);

            static::fail(Str\format(
                'Expected "%s" exception to be thrown.',
                Type\Exception\CoercionException::class
            ));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame('bool', $e->getTargetType());
            static::assertSame('int', $e->getActualType());
            static::assertSame('Could not coerce "int" to type "bool".', $e->getMessage());

            $trace  = $e->getTypeTrace();
            $frames = $trace->getFrames();

            static::assertCount(1, $frames);
            static::assertSame('iterable<bool, _>', Iter\first($frames));
        }
    }

    public function testIncorrectResourceType(): void
    {
        $type = Type\resource('curl');

        try {
            $type->coerce(new Collection\Map(['hello' => 'foo']));

            static::fail(Str\format(
                'Expected "%s" exception to be thrown.',
                Type\Exception\CoercionException::class
            ));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame('resource<curl>', $e->getTargetType());
            static::assertSame(Collection\Map::class, $e->getActualType());
            static::assertSame(Str\format(
                'Could not coerce "%s" to type "resource<curl>".',
                Collection\Map::class
            ), $e->getMessage());

            $trace  = $e->getTypeTrace();
            $frames = $trace->getFrames();

            static::assertCount(0, $frames);
        }
    }
}
