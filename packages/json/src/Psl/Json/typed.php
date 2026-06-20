<?php

declare(strict_types=1);

namespace Psl\Json;

use Psl\Type;

/**
 * Decode a json encoded string into a dynamic variable.
 *
 * @throws Exception\DecodeException If an error occurred.
 *
 * @api
 */
function typed<T>(string $json, Type\TypeInterface<T> $type): T
{
    try {
        return $type->coerce(namespace\decode($json));
    } catch (Type\Exception\CoercionException $e) {
        throw new Exception\DecodeException($e->getMessage(), (int) $e->getCode(), $e);
    }
}
