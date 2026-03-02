# OS

The `OS` component provides type-safe operating system detection. Instead of comparing raw strings against `PHP_OS_FAMILY`, you work with the `OperatingSystemFamily` enum and convenience functions.

## Usage

### Detecting the OS Family

@example('system/os-family.php')

### Convenience Checks

Quick boolean checks for the most common platforms:

@example('system/os-detection.php')

### The OperatingSystemFamily Enum

The enum covers six families: `Windows`, `BSD`, `Darwin`, `Solaris`, `Linux`, and `Unknown`. You can match against it for exhaustive platform handling:

@example('system/os-match.php')

`OperatingSystemFamily::default()` returns the family for the current runtime, making it useful as a dependency injection default.

See `src/Psl/OS/` for the full API.
