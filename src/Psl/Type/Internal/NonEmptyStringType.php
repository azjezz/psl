<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @extends Type\Type<non-empty-string>
 *
 * @internal
 */
final class NonEmptyStringType extends Type\Type
{
    /**
     * @psalm-param mixed $value
     *
     * @psalm-return non-empty-string
     *
     * @throws CoercionException
     */
    public function coerce($value): string
    {
        if (Type\is_string($value) && !Str\is_empty($value)) {
            return $value;
        }

        if (Type\is_int($value)) {
            $str = (string) $value;
            if (!Str\is_empty($str)) {
                /** @var non-empty-string $str */
                return $str;
            }
        }

        if (Type\is_object($value) && method_exists($value, '__toString')) {
            $str = (string)$value;
            if (!Str\is_empty($str)) {
                return $str;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return non-empty-string
     *
     * @psalm-assert non-empty-string $value
     *
     * @throws AssertException
     */
    public function assert($value): string
    {
        if (Type\is_string($value) && !Str\is_empty($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'non-empty-string';
    }
}
