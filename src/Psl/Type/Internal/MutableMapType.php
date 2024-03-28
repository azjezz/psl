<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Collection;
use Psl\Dict;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_iterable;
use function is_object;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<Collection\MutableMapInterface<Tk, Tv>>
 *
 * @internal
 */
final class MutableMapType extends Type\Type
{
    /**
     * @param Type\TypeInterface<Tk> $key_type
     * @param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        private readonly Type\TypeInterface $key_type,
        private readonly Type\TypeInterface $value_type
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @return Collection\MutableMapInterface<Tk, Tv>
     */
    public function coerce(mixed $value): Collection\MutableMapInterface
    {
        if (is_iterable($value)) {
            /** @var Type\Type<Tk> $key_type */
            $key_type = $this->key_type;
            /** @var Type\Type<Tv> $value_type */
            $value_type = $this->value_type;

            /** @var list<array{Tk, Tv}> $entries */
            $entries = [];

            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                $entries[] = [
                    $key_type->coerce($k),
                    $value_type->coerce($v),
                ];
            }

            $dict = Dict\from_entries($entries);

            /** @var Collection\MutableMap<Tk, Tv> */
            return new Collection\MutableMap($dict);
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return Collection\MutableMapInterface<Tk, Tv>
     *
     * @psalm-assert Collection\MutableMapInterface<Tk, Tv> $value
     */
    public function assert(mixed $value): Collection\MutableMapInterface
    {
        if (is_object($value) && $value instanceof Collection\MutableMapInterface) {
            /** @var Type\Type<Tk> $key_type */
            $key_type = $this->key_type;
            /** @var Type\Type<Tv> $value_type */
            $value_type = $this->value_type;

            /** @var list<array{Tk, Tv}> $entries */
            $entries = [];

            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                $entries[] = [
                    $key_type->assert($k),
                    $value_type->assert($v),
                ];
            }

            $dict = Dict\from_entries($entries);

            /** @var Collection\MutableMap<Tk, Tv> */
            return new Collection\MutableMap($dict);
        }

        throw AssertException::withValue($value, $this->toString());
    }

    public function toString(): string
    {
        return Str\format(
            '%s<%s, %s>',
            Collection\MutableMapInterface::class,
            $this->key_type->toString(),
            $this->value_type->toString(),
        );
    }
}
