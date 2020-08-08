<?php

declare(strict_types=1);

namespace Psl\Json;

use function json_encode;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use JsonException;
use Psl\Str;

/**
 * Returns a string containing the JSON representation of the supplied value.
 *
 * @param mixed $value
 *
 * @throws Exception\JsonEncodeException If an error occurred.
 */
function encode($value, bool $pretty = false, int $flags = 0): string
{
    $flags |= JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_PRESERVE_ZERO_FRACTION
        | JSON_THROW_ON_ERROR;

    if ($pretty) {
        $flags |= JSON_PRETTY_PRINT;
    }

    try {
        $json = json_encode($value, $flags);
    } catch (JsonException $e) {
        throw new Exception\JsonEncodeException(Str\format('%s.', $e->getMessage()), (int)$e->getCode(), $e);
    }

    return $json;
}
