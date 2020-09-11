<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @extends Type\Type<resource>
 *
 * @internal
 */
final class ResourceType extends Type\Type
{
    private ?string $kind;

    public function __construct(?string $kind = null)
    {
        $this->kind = $kind;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return resource
     *
     * @throws CoercionException
     */
    public function coerce($value)
    {
        if (Type\is_resource($value)) {
            $kind = $this->kind;
            if (null === $kind) {
                return $value;
            }

            if (get_resource_type($value) === $kind) {
                return $value;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return resource
     *
     * @psalm-assert resource $value
     *
     * @throws AssertException
     */
    public function assert($value)
    {
        if (Type\is_resource($value)) {
            $kind = $this->kind;
            if (null === $kind) {
                return $value;
            }

            if (get_resource_type($value) === $kind) {
                return $value;
            }
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        if (Type\is_null($this->kind)) {
            return 'resource';
        }

        return Str\format('resource<%s>', $this->kind);
    }
}
