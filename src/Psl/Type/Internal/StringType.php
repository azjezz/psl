<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;
use function is_object;
use function is_string;

/**
 * @extends Type\Type<string>
 *
 * @internal
 */
final class StringType extends Type\Type
{
    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true string $value
     */
    public function matches($value): bool
    {
        return is_string($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws CoercionException
     */
    public function coerce($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @psalm-assert string $value
     *
     * @throws AssertException
     */
    public function assert($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'string';
    }
}
