<?php

declare(strict_types=1);

namespace Psl\Json;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\TypeInterface;

/**
 * Decode a json encoded string into a dynamic variable.
 *
 * @psalm-template T
 *
 * @psalm-param TypeInterface<T> $type
 *
 * @psalm-return T
 *
 * @throws Exception\DecodeException If an error occurred.
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
