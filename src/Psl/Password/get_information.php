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
 * @psalm-return array{algorithm: string, options: array{cost: int}|array{memory_cost: int, time_cost: int, threads: int}}
 */
function get_information(string $hash): array
{
    $information = password_get_info($hash);
    $algorithm = (string)$information['algoName'];
    if (BCRYPT_ALGORITHM === $algorithm) {
        return [
            'algorithm' => $algorithm,
            'options' => [
                'cost' => (int)($information['options']['cost'] ?? BCRYPT_DEFAULT_COST),
            ],
        ];
    }

    return [
        'algorithm' => $algorithm,
        'options' => [
            'memory_cost' => (int)($information['options']['memory_cost'] ?? ARGON2_DEFAULT_MEMORY_COST),
            'time_cost' => (int)($information['options']['time_cost'] ?? ARGON2_DEFAULT_TIME_COST),
            'threads' => (int)($information['options']['threads'] ?? ARGON2_DEFAULT_THREADS),
        ],
    ];
}
