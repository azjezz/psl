<?php

declare(strict_types=1);

namespace Psl\Json;

use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * Decode a json encoded string into a dynamic variable.
 *
 * @psalm-template T
 *
 * @psalm-param Type<T> $type
 *
 * @psalm-return T
 *
 * @throws Exception\JsonDecodeException If an error occurred.
 */
function typed(string $json, Type $type)
{
    $value = decode($json);

    try {
        return $type->assert($value);
    } catch (TypeAssertException $e) {
    }

    try {
        return $type->coerce($value);
    } catch (TypeCoercionException $e) {
        throw new Exception\JsonDecodeException($e->getMessage(), (int)$e->getCode(), $e);
    }
}
