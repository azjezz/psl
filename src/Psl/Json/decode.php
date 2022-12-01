<?php

declare(strict_types=1);

namespace Psl\Json;

use JsonException;
use Psl\Str;

use function json_decode;

use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;

/**
 * Decode a json encoded string into a dynamic variable.
 *
 * @throws Exception\DecodeException If an error occurred.
 *
 * @pure
 */
function decode(string $json, bool $assoc = true): mixed
{
    try {
        /** @var mixed $value */
        $value = json_decode(
            $json,
            $assoc,
            512,
            JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR,
        );
    } catch (JsonException $e) {
        throw new Exception\DecodeException(Str\format('%s.', $e->getMessage()), $e->getCode(), $e);
    }

    return $value;
}
