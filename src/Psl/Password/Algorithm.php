<?php

declare(strict_types=1);

namespace Psl\Password;

use const PASSWORD_ARGON2I;
use const PASSWORD_ARGON2ID;
use const PASSWORD_BCRYPT;

enum Algorithm: string
{
    /**
     * The default algorithm to use for hashing if no algorithm is provided.
     * This may change in newer PHP releases when newer, stronger hashing algorithms are supported.
     *
     * It is worth noting that over time this constant can (and likely will) change.
     * Therefore you should be aware that the length of the resulting hash can change.
     *
     * Therefore, if you use `Psl\Password\DEFAULT_ALGORITHM` you should store
     * the resulting hash in a way that can store more than 60 characters (255 is the recommended width).
     */
    case Default = 'default';

    /**
     * The `BCRYPT_ALGORITHM` is used to create new password hashes
     * using the blowfish algorithm.
     *
     * This will result in a hash using the `"$2y$"` crypt format
     * which is always 60 characters wide.
     *
     * Supported options:
     *      - `cost` ( integer ): which denotes the algorithmic cost that should be used.
     */
    case Bcrypt = 'bcrypt';

    /**
     * The `Argon2i` is used to create new password hashes using the argon2i algorithm.
     *
     * Supported options:
     *      - `memory_cost` ( integer ): Maximum memory (in bytes) that may be used to compute the Argon2 hash.
     *      - `time_cost` ( integer ): Maximum amount of time it may take to compute the Argon2 hash.
     *      - `threads ` ( integer ): Number of threads to use for computing the Argon2 hash
     */
    case Argon2i = 'argon2i';

    /**
     * The `Argon2id` is used to create new password hashes using the argon2id algorithm.
     *
     * Supported options:
     *      - `memory_cost` ( integer ): Maximum memory (in bytes) that may be used to compute the Argon2 hash.
     *      - `time_cost` ( integer ): Maximum amount of time it may take to compute the Argon2 hash.
     *      - `threads ` ( integer ): Number of threads to use for computing the Argon2 hash
     */
    case Argon2id = 'argon2id';

    /**
     * @mutation-free
     */
    public function getBuiltinConstantValue(): string
    {
        return match ($this) {
            static::Default, static::Bcrypt => PASSWORD_BCRYPT,
            static::Argon2i => PASSWORD_ARGON2I,
            static::Argon2id => PASSWORD_ARGON2ID,
        };
    }
}
