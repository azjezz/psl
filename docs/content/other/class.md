# Class

The `Class` component provides type-safe wrappers around PHP's built-in class reflection and existence checks. It replaces loose `class_exists()` calls with a consistent, intention-revealing API and adds helpers for inspecting class properties.

## Why Use This?

PHP's native `class_exists()` accepts a second argument to control autoloading, which is easy to forget or misuse. PSL separates that choice into two distinct functions -- `exists()` (triggers autoloading) and `defined()` (checks only already-loaded definitions) -- so the intent is always clear.

## Usage

### Checking Existence

@example('other/reflection-exists.php')

### Inspecting Classes

@example('other/reflection-inspect.php')

### Practical Example

Guard a factory method against invalid input:

@example('other/reflection-factory.php')

See `src/Psl/Class/` for the full API.
