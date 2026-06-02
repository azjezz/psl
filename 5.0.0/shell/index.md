# Shell

The `Shell` component provides a single function, `Shell\execute()`, for running external commands and capturing their output. It handles argument escaping, error output management, and proper exception handling for failed commands.

For more advanced process management (streaming I/O, signals, non-blocking operation), see the `Process` component. `Shell` is the simpler choice when you just need to run a command and get the result.

## Basic Usage

```php
use Psl\IO;
use Psl\Shell;

// Run a command and capture stdout
$output = Shell\execute('echo', ['Hello from shell']);
IO\write_line('Output: %s', $output);

// With a specific working directory
$output = Shell\execute('ls', ['-la'], __DIR__);
IO\write_line('Directory listing:\n%s', $output);

// With environment variables
$output = Shell\execute(
    'php',
    ['-r', 'echo getenv("APP_ENV");'],
    environment: [
        'APP_ENV' => 'production',
        'DEBUG' => '0',
    ],
);
IO\write_line('Env value: %s', $output);
```

## Handling Error Output

By default, stderr is discarded. The `ErrorOutputBehavior` enum controls how stderr is handled relative to stdout:

```php
use Psl\IO;
use Psl\Shell;
use Psl\Shell\ErrorOutputBehavior;

// Discard stderr (default)
$stdout = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    error_output_behavior: ErrorOutputBehavior::Discard,
);
IO\write_line('Discard:  stdout=%s', $stdout);

// Append stderr after stdout
$combined = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    error_output_behavior: ErrorOutputBehavior::Append,
);
IO\write_line('Append:   %s', $combined);

// Prepend stderr before stdout
$combined = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    error_output_behavior: ErrorOutputBehavior::Prepend,
);
IO\write_line('Prepend:  %s', $combined);

// Return only stderr, discard stdout
$stderr = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    error_output_behavior: ErrorOutputBehavior::Replace,
);
IO\write_line('Replace:  %s', $stderr);

// Pack both streams for later separation
$packed = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    error_output_behavior: ErrorOutputBehavior::Packed,
);
[$stdout, $stderr] = Shell\unpack($packed);
IO\write_line('Packed:   stdout=%s, stderr=%s', $stdout, $stderr);
```

## Timeouts

Pass a `Duration` to limit how long the command can run:

```php
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Shell;

try {
    $output = Shell\execute('sleep', ['10'], timeout: Duration::seconds(1));
} catch (Shell\Exception\TimeoutException) {
    IO\write_line('Command exceeded the time limit (as expected)');
}
```

## Error Handling

`execute()` throws specific exceptions depending on what went wrong:

- **`FailedExecutionException`** -- the command exited with a non-zero status. Provides `getCommand()`, `getOutput()`, and `getErrorOutput()` for inspection.
- **`PossibleAttackException`** -- the command or an argument contains a NULL byte, indicating a potential injection attack.
- **`TimeoutException`** -- the command exceeded the specified timeout duration.
- **`RuntimeException`** -- the working directory does not exist, or the process could not be created.

```php
use Psl\IO;
use Psl\Shell;

try {
    $output = Shell\execute('php', ['-r', 'exit(1);']);
} catch (Shell\Exception\FailedExecutionException $e) {
    IO\write_line('Command failed: %s', $e->getCommand());
    IO\write_line('Exit code: %d', $e->getCode());
    IO\write_line('Stderr: %s', $e->getErrorOutput());
}
```

See [src/Psl/Shell/](https://github.com/php-standard-library/php-standard-library/tree/5.0.0/src/Psl/Shell/) for the full API.
