# Reflection

The `Class`, `Interface`, and `Trait` components provide type-safe wrappers around PHP's built-in reflection and existence checks. They replace loose `class_exists()` / `interface_exists()` / `trait_exists()` calls with a consistent, intention-revealing API and add helpers for inspecting class properties.

## Why Use These?

PHP's native `class_exists()` accepts a second argument to control autoloading, which is easy to forget or misuse. PSL separates that choice into two distinct functions -- `exists()` (triggers autoloading) and `defined()` (checks only already-loaded definitions) -- so the intent is always clear.

## Usage

### Checking Existence

@example('other/reflection-exists.php')

The same pair is available for interfaces and traits:

@example('other/reflection-interface-trait.php')

### Inspecting Classes

The `Class` component also exposes reflection helpers:

@example('other/reflection-inspect.php')

### Practical Example

Guard a factory method against invalid input:

@example('other/reflection-factory.php')

See `src/Psl/Class/`, `src/Psl/Interface/`, and `src/Psl/Trait/` for the full API.
