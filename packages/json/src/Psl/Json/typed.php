<?php

declare(strict_types=1);

namespace Psl\Json;

use Psl\Type;

/**
 * Decode a json encoded string into a dynamic variable.
 *
 * @param Type\TypeInterface<T> $type
 *
 * @throws Exception\DecodeException If an error occurred.
 *
 * @return T
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
