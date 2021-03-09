<?php

declare(strict_types=1);

namespace Psl\Json;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\TypeInterface;

/**
 * Decode a json encoded string into a dynamic variable.
 *
 * @template T
 *
 * @param TypeInterface<T> $type
 *
 * @throws Exception\DecodeException If an error occurred.
 *
 * @return T
 */
function typed(string $json, TypeInterface $type)
{
    $value = decode($json);

    try {
        return $type->assert($value);
    } catch (AssertException $e) {
    }

    try {
        return $type->coerce($value);
    } catch (CoercionException $e) {
        throw new Exception\DecodeException($e->getMessage(), (int)$e->getCode(), $e);
    }
}
