# Hash

The `Hash` component provides cryptographic and non-cryptographic hashing through a type-safe API. It wraps PHP's hash functions with an `Algorithm` enum that prevents typos and invalid algorithm names, and includes timing-safe comparison and HMAC support.

## Hashing Data

`Hash\hash()` computes a message digest for the given data and algorithm:

@example('security/hash-compute.php')

The `Hash\Algorithm` enum covers all algorithms available in PHP, including SHA-1, SHA-256, SHA-512, SHA-3 variants, MD5, CRC32, xxHash, MurmurHash, and many more.

## Comparing Hashes Safely

Never compare hashes with `===`. String comparison leaks timing information that can be exploited to guess hashes byte-by-byte. Use `Hash\equals()` instead, which runs in constant time:

@example('security/hash-equals.php')

## Incremental Hashing

For large data or streaming scenarios, use `Hash\Context` to feed data in chunks:

@example('security/hash-incremental.php')

The context is immutable -- each `update()` returns a new context, so you can safely branch from any intermediate state.

## HMAC

HMAC (Hash-based Message Authentication Code) combines a hash with a secret key to verify both data integrity and authenticity. Use it when you need to ensure data has not been tampered with and was produced by someone who holds the key.

@example('security/hash-hmac.php')

A practical example -- signing and verifying an API request:

@example('security/hash-hmac-verify.php')

HMAC also supports incremental hashing through `Hash\Context::hmac()`:

@example('security/hash-hmac-incremental.php')

See `src/Psl/Hash/` for the full API.
