# Password

The `Password` component provides secure password hashing and verification. It wraps PHP's `password_*` functions with a type-safe `Algorithm` enum and a clean functional API, making it easy to hash passwords with bcrypt or Argon2 and handle algorithm upgrades over time.

## Hashing a Password

@example('security/password-hash.php')

You can tune algorithm parameters through the `$options` array:

@example('security/password-hash-options.php')

## Verifying a Password

`verify()` checks a plaintext password against a stored hash. The algorithm and salt are embedded in the hash itself, so you do not need to store them separately:

@example('security/password-verify.php')

## Checking If a Rehash Is Needed

When you upgrade your hashing algorithm or increase the cost factor, existing hashes remain valid but are no longer up to your current standard. `needs_rehash()` detects this so you can transparently upgrade hashes at login time:

@example('security/password-rehash.php')

This pattern lets you migrate from bcrypt to Argon2id (or increase cost parameters) gradually, without forcing all users to reset their passwords.

## Inspecting a Hash

`get_information()` returns the algorithm and options used to create a hash:

@example('security/password-info.php')

## Algorithm

The `Password\Algorithm` enum provides four cases:

- `Default` -- follows PHP's default, which may change over time to adopt stronger algorithms
- `Bcrypt` -- Blowfish-based, always produces 60-character hashes
- `Argon2i` -- memory-hard algorithm resistant to GPU attacks
- `Argon2id` -- hybrid of Argon2i and Argon2d, recommended for most applications

When using `Algorithm::Default`, store hashes in a column that can hold at least 255 characters, since the underlying algorithm (and hash length) may change in future PHP versions.

See `src/Psl/Password/` for the full API.
