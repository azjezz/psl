<?php

declare(strict_types=1);

namespace Psl\Json;

use Psl\Type;

/**
 * Decode a json encoded string into a dynamic variable.
 *
 * @template T
 *
 * @param Type\TypeInterface<T> $type
 *
 * @throws Exception\DecodeException If an error occurred.
 *
 * @return T
 */
function typed(string $json, Type\TypeInterface $type): mixed
{
    try {
        return $type->coerce(decode($json));
    } catch (Type\Exception\CoercionException $e) {
        throw new Exception\DecodeException($e->getMessage(), $e->getCode(), $e);
    }
}
