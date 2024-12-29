<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @param ?string $kind The resource kind, if null, the resource type won't be validated.
 *
 * @return TypeInterface<resource>
 */
function resource(null|string $kind = null): TypeInterface
{
    return new Internal\ResourceType($kind);
}
