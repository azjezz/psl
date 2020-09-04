<?php

declare(strict_types=1);

namespace Psl\Password;

use function password_get_info;
use Psl;

/**
 * Returns information about the given hash.
 *
 * When passed in a valid hash created by an algorithm supported by `Psl\Password\hash()`,
 * this function will return an array of information about that hash.
 *
 * @psalm-return {algorithm: string, options: array{cost: int}|array{memory_cost: int, time_cost: int, threads: int}}
 */
function get_information(string $hash): array
{
    $information = password_get_info($hash);

    return [
        'algorithm' => $information['algoName'],
        'options' => $information['options']
    ];
}
