<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_iterable;

/**
 * @template Tk
 * @template Tv
 *
 * @extends Type\Type<iterable<Tk, Tv>>
 *
 * @internal
 */
final class IterableType extends Type\Type
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
     * @return iterable<Tk, Tv>
     */
    public function coerce(mixed $value): iterable
    {
        if (is_iterable($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('iterable<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('iterable<_, %s>', $this->value_type->toString()));

            /** @var Type\Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @var Type\Type<Tv> $value_type_speec */
            $value_type = $this->value_type->withTrace($value_trace);

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

            /** @var iterable<Tk, Tv> */
            return Iter\Iterator::from((static function () use ($entries) {
                foreach ($entries as [$key, $value]) {
                    yield $key => $value;
                }
            }));
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return iterable<Tk, Tv>
     *
     * @psalm-assert iterable<Tk, Tv> $value
     */
    public function assert(mixed $value): iterable
    {
        if (is_iterable($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('iterable<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('iterable<_, %s>', $this->value_type->toString()));

            /** @var Type\Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @var Type\Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

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

            /** @var iterable<Tk, Tv> */
            return Iter\Iterator::from((static function () use ($entries) {
                foreach ($entries as [$key, $value]) {
                    yield $key => $value;
                }
            }));
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('iterable<%s, %s>', $this->key_type->toString(), $this->value_type->toString());
    }
}
