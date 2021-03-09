<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Dict;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_array;
use function is_iterable;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<array<Tk, Tv>>
 *
 * @internal
 */
final class DictType extends Type\Type
{
    /**
     * @var Type\TypeInterface<Tk>
     */
    private Type\TypeInterface $key_type;

    /**
     * @var Type\TypeInterface<Tv>
     */
    private Type\TypeInterface $value_type;

    /**
     * @param Type\TypeInterface<Tk> $key_type
     * @param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        Type\TypeInterface $key_type,
        Type\TypeInterface $value_type
    ) {
        $this->key_type   = $key_type;
        $this->value_type = $value_type;
    }

    /**
     * @param mixed $value
     *
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    public function coerce($value): array
    {
        if (is_iterable($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('array<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('array<_, %s>', $this->value_type->toString()));

            $key_type = $this->key_type->withTrace($key_trace);
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @var list<array{0: Tk, 1: Tv}> $entries
             */
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

            return Dict\from_entries($entries);
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @param mixed $value
     *
     * @throws AssertException
     *
     * @return array<Tk, Tv>
     *
     * @psalm-assert array<Tk, Tv> $value
     */
    public function assert($value): array
    {
        if (is_array($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('array<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('array<_, %s>', $this->value_type->toString()));

            $key_type = $this->key_type->withTrace($key_trace);
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @var list<array{0: Tk, 1: Tv}> $entries
             */
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

            return Dict\from_entries($entries);
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('array<%s, %s>', $this->key_type->toString(), $this->value_type->toString());
    }
}
