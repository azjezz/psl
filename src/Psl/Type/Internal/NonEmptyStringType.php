<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;
use function is_object;
use function is_string;

/**
 * @extends Type\Type<non-empty-string>
 *
 * @internal
 */
final class NonEmptyStringType extends Type\Type
{
    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true non-empty-string $value
     */
    public function matches($value): bool
    {
        return is_string($value) && !Str\is_empty($value);
    }

    /**
     * @param mixed $value
     *
     * @throws CoercionException
     *
     * @return non-empty-string
     */
    public function coerce($value): string
    {
        if (is_string($value) && !Str\is_empty($value)) {
            return $value;
        }

        if (is_int($value)) {
            $str = (string) $value;
            if (!Str\is_empty($str)) {
                /** @var non-empty-string $str */
                return $str;
            }
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $str = (string)$value;
            if (!Str\is_empty($str)) {
                return $str;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @param mixed $value
     *
     * @throws AssertException
     *
     * @return non-empty-string
     *
     * @psalm-assert non-empty-string $value
     */
    public function assert($value): string
    {
        if (is_string($value) && !Str\is_empty($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'non-empty-string';
    }
}
