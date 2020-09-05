<?php

declare(strict_types=1);

namespace Psl\Password;

use const PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
use const PASSWORD_ARGON2_DEFAULT_THREADS;
use const PASSWORD_ARGON2_DEFAULT_TIME_COST;
use const PASSWORD_ARGON2I;
use const PASSWORD_ARGON2ID;
use const PASSWORD_BCRYPT_DEFAULT_COST;

/**
 * The `BCRYPT_ALGORITHM` is used to create new password hashes
 * using the blowfish algorithm.
 *
 * This will result in a hash using the `"$2y$"` crypt format
 * which is always 60 characters wide.
 *
 * Supported options:
 *      - `cost` ( integer ): which denotes the algorithmic cost that should be used.
 *          Defaults to `Psl\Password\BCRYPT_DEFAULT_COST`
 *
 * @var string
 */
const BCRYPT_ALGORITHM = 'bcrypt';

/**
 * The default algorithm to use for hashing if no algorithm is provided.
 * This may change in newer PHP releases when newer, stronger hashing algorithms are supported.
 *
 * It is worth noting that over time this constant can (and likely will) change.
 * Therefore you should be aware that the length of the resulting hash can change.
 *
 * Therefore, if you use `Psl\Password\DEFAULT_ALGORITHM` you should store
 * the resulting hash in a way that can store more than 60 characters (255 is the recommended width).
 *
 * @var string
 */
const DEFAULT_ALGORITHM = BCRYPT_ALGORITHM;

/**
 * Default bcrypt cost that will be used while trying to compute a hash.
 *
 * @var string
 */
const BCRYPT_DEFAULT_COST = PASSWORD_BCRYPT_DEFAULT_COST;

/**
 * The `BCRYPT_ALGORITHM` is used to create new password hashes
 * using the argon2i algorithm.
 *
 * Supported options:
 *      - `memory_cost` ( integer ): Maximum memory (in bytes) that may be used to compute the Argon2 hash.
 *          Defaults to `Psl\Password\ARGON2_DEFAULT_MEMORY_COST`.
 *      - `time_cost` ( integer ): Maximum amount of time it may take to compute the Argon2 hash.
 *          Defaults to `Psl\Password\ARGON2_DEFAULT_TIME_COST`.
 *      - `threads ` ( integer ): Number of threads to use for computing the Argon2 hash
 *          Defaults to `Psl\Password\ARGON2_DEFAULT_THREADS`.
 *
 * @var string
 */
const ARGON2I_ALGORITHM = PASSWORD_ARGON2I;

/**
 * The `BCRYPT_ALGORITHM` is used to create new password hashes
 * using the argon2id algorithm.
 *
 * It supports the same options as `Psl\Password\ARGON2I_ALGORITHM`
 *
 * @var string
 */
const ARGON2ID_ALGORITHM = PASSWORD_ARGON2ID;

/**
 * Default amount of memory in bytes that Argon2lib will use while trying to compute a hash.
 *
 * @var int
 */
const ARGON2_DEFAULT_MEMORY_COST = PASSWORD_ARGON2_DEFAULT_MEMORY_COST;

/**
 * Default amount of time that Argon2lib will spend trying to compute a hash.
 *
 * @var int
 */
const ARGON2_DEFAULT_TIME_COST = PASSWORD_ARGON2_DEFAULT_TIME_COST;

/**
 * Default number of threads that Argon2lib will use.
 *
 * @var int
 */
const ARGON2_DEFAULT_THREADS = PASSWORD_ARGON2_DEFAULT_THREADS;
