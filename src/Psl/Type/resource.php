<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @param ?string $kind The resource kind, if null, the resource type won't be validated.
 *
 * @return TypeInterface<resource>
 */
function resource(?string $kind = null): TypeInterface
{
    return new Internal\ResourceType($kind);
}
