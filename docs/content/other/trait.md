# Trait

The `Trait` component provides type-safe wrappers around PHP's built-in trait reflection and existence checks. It replaces loose `trait_exists()` calls with a consistent, intention-revealing API.

## Usage

PSL separates existence checks into two distinct functions -- `exists()` (triggers autoloading) and `defined()` (checks only already-loaded definitions) -- so the intent is always clear.

@example('other/reflection-interface-trait.php')

See `src/Psl/Trait/` for the full API.
