<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_resource;

/**
 * @extends Type\Type<resource>
 *
 * @internal
 */
final readonly class ResourceType extends Type\Type
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(
        private null|string $kind = null,
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @return resource
     */
    #[\Override]
    public function coerce(mixed $value): mixed
    {
        if (is_resource($value)) {
            $kind = $this->kind;
            if (null === $kind) {
                return $value;
            }

            if (get_resource_type($value) === $kind) {
                return $value;
            }
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return resource
     *
     * @psalm-assert resource $value
     */
    #[\Override]
    public function assert(mixed $value): mixed
    {
        if (is_resource($value)) {
            $kind = $this->kind;
            if (null === $kind) {
                return $value;
            }

            if (get_resource_type($value) === $kind) {
                return $value;
            }
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        if (null === $this->kind) {
            return 'resource';
        }

        return Str\format('resource (%s)', $this->kind);
    }
}
