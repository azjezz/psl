# PseudoRandom

The `PseudoRandom` component provides fast pseudo-random number generation using PHP's Mersenne Twister engine (`mt_rand`). It is appropriate for non-security contexts such as shuffling, sampling, simulations, or any case where speed matters more than cryptographic guarantees.

> **Important**: Do not use `PseudoRandom` for tokens, passwords, keys, or anything security-related. Use `SecureRandom` instead.

## Usage

### Random Integers

Generate a pseudo-random integer within a range:

@example('security/pseudo-random-int.php')

### Random Floats

Generate a pseudo-random float between 0.0 and 1.0:

@example('security/pseudo-random-float.php')

## When to Use PseudoRandom vs SecureRandom

| Use case | Component |
|---|---|
| Dice rolls, shuffling, sampling | `PseudoRandom` |
| Simulation / Monte Carlo | `PseudoRandom` |
| API tokens, session IDs | `SecureRandom` |
| Passwords, encryption keys | `SecureRandom` |
| Nonces, CSRF tokens | `SecureRandom` |

See `src/Psl/PseudoRandom/` for the full API.
