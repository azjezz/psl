# SecureRandom

The `SecureRandom` component provides cryptographically secure random data generation. It wraps PHP's `random_bytes()` and `random_int()` with a consistent API and typed exceptions, making it suitable for security-sensitive tasks like token generation, password creation, and nonce generation.

## Usage

### Random Integers

Generate a secure random integer within a range:

@example('security/secure-random-int.php')

### Random Floats

Generate a secure random float between 0.0 and 1.0:

@example('security/secure-random-float.php')

### Random Bytes

Generate raw random bytes, useful for cryptographic keys or binary tokens:

@example('security/secure-random-bytes.php')

### Random Strings

Generate a random string from an alphabet. Defaults to alphanumeric characters:

@example('security/secure-random-string.php')

## Error Handling

All functions throw `SecureRandom\Exception\InsufficientEntropyException` if the system cannot gather enough entropy, and `SecureRandom\Exception\InvalidArgumentException` for invalid arguments (such as `$min > $max` for `int()`).

See `src/Psl/SecureRandom/` for the full API.
