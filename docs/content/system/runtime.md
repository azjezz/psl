# Runtime

The `Runtime` component provides introspection into the PHP runtime environment. It exposes version information, loaded extensions, the active SAPI, and build flags through simple function calls.

## Usage

### PHP Version

@example('system/runtime-version.php')

### Server API (SAPI)

Determine how PHP is being invoked:

@example('system/runtime-sapi.php')

### Extensions

Check for loaded extensions or list them all:

@example('system/runtime-extensions.php')

### Zend Engine

@example('system/runtime-zend.php')

### Build Flags

@example('system/runtime-build-flags.php')

See `src/Psl/Runtime/` for the full API.
