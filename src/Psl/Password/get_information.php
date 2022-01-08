<?php

declare(strict_types=1);

namespace Psl\Password;

use Psl;

use function password_get_info;

use const PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
use const PASSWORD_ARGON2_DEFAULT_THREADS;
use const PASSWORD_ARGON2_DEFAULT_TIME_COST;
use const PASSWORD_ARGON2ID;
use const PASSWORD_BCRYPT;
use const PASSWORD_BCRYPT_DEFAULT_COST;

/**
 * Returns information about the given hash.
 *
 * When passed in a valid hash created by an algorithm supported by `Psl\Password\hash()`,
 * this function will return an array of information about that hash.
 *
 * @return array{algorithm: Algorithm, options: array{cost: int}|array{memory_cost: int, time_cost: int, threads: int}}
 *
 * @pure
 */
function get_information(string $hash): array
{
    /** @var array{algo: string, options: array<string, array-key>} $information */
    $information = password_get_info($hash);
    $algorithm   = $information['algo'];
    if (PASSWORD_BCRYPT === $algorithm) {
        return [
            'algorithm' => Algorithm::Bcrypt,
            'options' => [
                'cost' => (int)($information['options']['cost'] ?? PASSWORD_BCRYPT_DEFAULT_COST),
            ],
        ];
    }

    return [
        'algorithm' => PASSWORD_ARGON2ID === $algorithm ? Algorithm::Argon2id : Algorithm::Argon2i,
        'options' => [
            'memory_cost' => (int)($information['options']['memory_cost'] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST),
            'time_cost' => (int)($information['options']['time_cost'] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST),
            'threads' => (int)($information['options']['threads'] ?? PASSWORD_ARGON2_DEFAULT_THREADS),
        ],
    ];
}
