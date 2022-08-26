<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @extends Type\Type<array<array-key, mixed>>
 *
 * @internal
 */
final class MixedDictType extends Type\Type
{
    /**
     * @throws CoercionException
     *
     * @return array<array-key, mixed>
     */
    public function coerce(mixed $value): array
    {
        if (! is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        if (is_array($value)) {
            return $value;
        }

        $result = [];

        $key_type = Type\array_key();
        $key_type = $key_type->withTrace($this->getTrace()->withFrame('dict<' . $key_type->toString() . ', _>'));

        /**
         * @var array-key $k
         * @var mixed $v
         *
         * @psalm-suppress MixedAssignment
         */
        foreach ($value as $k => $v) {
            $result[$key_type->coerce($k)] = $v;
        }

        return $result;
    }

    /**
     * @throws AssertException
     *
     * @return array<array-key, mixed>
     *
     * @psalm-assert array<array-key, mixed> $value
     */
    public function assert(mixed $value): array
    {
        if (! is_array($value)) {
            throw AssertException::withValue($value, $this->toString(), $this->getTrace());
        }

        return $value;
    }

    public function toString(): string
    {
        return 'dict<array-key, mixed>';
    }
}
