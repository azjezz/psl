# IO

The `IO` component provides handle-based I/O abstractions. Instead of reaching for global functions like `fread()` and `fwrite()`, you work with typed handle interfaces that make I/O composable, testable, and safe.

## Handle Interfaces

Handles are defined as narrow interfaces, each describing a single capability:

- **`ReadHandleInterface`** -- read bytes, check for EOF
- **`WriteHandleInterface`** -- write bytes
- **`SeekHandleInterface`** -- move the cursor position
- **`CloseHandleInterface`** -- explicitly close the handle

A concrete handle implements whichever combination applies. For example, a file handle implements all four, while a network socket implements read, write, and close but not seek.

## Quick Output

Convenience functions write directly to stdout or stderr:

```php
use Psl\IO;

$msg = 'file not found';

IO\write('Hello, %s!', 'world'); // stdout, no newline
IO\write_line('Count: %d', 42); // stdout + newline
IO\write_error('something went wrong'); // stderr
IO\write_error_line('Error: %s', $msg); // stderr + newline
```

All output functions support `sprintf`-style formatting.

## Standard Handles

Three functions return the process-level I/O handles:

```php
use Psl\IO;

$stdin = IO\input_handle(); // ReadHandleInterface  (STDIN in CLI)
$stdout = IO\output_handle(); // WriteHandleInterface (STDOUT in CLI)
$stderr = IO\error_handle(); // WriteHandleInterface (STDERR in CLI, null otherwise)
```

In non-CLI SAPIs, `input_handle()` reads from `php://input` and `output_handle()` writes to `php://output`. `error_handle()` returns `null` outside CLI.

## MemoryHandle

`MemoryHandle` is an in-memory buffer implementing read, write, seek, and close. It is useful for testing code that accepts handle interfaces without touching the filesystem or network.

```php
use Psl\IO;

$handle = new IO\MemoryHandle();
$handle->writeAll('Hello, World!');
$handle->seek(0);
$handle->readAll(); // 'Hello, World!'
$_ = $handle->getBuffer(); // 'Hello, World!' (always returns full buffer)

// Use MemoryHandle as a test double for any WriteHandleInterface
function render_report(IO\WriteHandleInterface $output): void
{
    $output->writeAll("Report\n");
}

$buffer = new IO\MemoryHandle();
render_report($buffer);
Psl\invariant($buffer->getBuffer() === "Report\n", 'Expected buffer to contain "Report\n"');
```

## Reader

`Reader` wraps any `ReadHandleInterface` with buffered, higher-level reading methods:

```php
use Psl\IO;

$handle = new IO\MemoryHandle("line one\nline two\n\x00rest");
$reader = new IO\Reader($handle);

$line = $reader->readLine(); // read until newline: "line one"
$byte = $reader->readByte(); // read exactly one byte: "l"
$chunk = $reader->readFixedSize(7); // read exactly 7 bytes: "ine two"
$until = $reader->readUntil("\0"); // read until a delimiter (delimiter consumed but not returned): "\n"

IO\write_line('Line:  %s', $line ?? '<line not found>');
IO\write_line('Byte:  %s', $byte);
IO\write_line('Chunk: %s', $chunk);
IO\write_line('Until: %s', $until ?? '<suffix not found>');
```

## Spool

`IO\spool()` creates a handle that writes to memory until a threshold is reached (default 2MB), then transparently spools to a temporary file on disk. This is useful when buffering data of unknown size without risking excessive memory usage.

```php
use Psl\IO;
use Psl\Str;

// Writes to memory until 2MB, then spools to a temporary file on disk
$handle = IO\spool();

$handle->writeAll('Hello, World!');
$handle->seek(0);
$handle->readAll(); // 'Hello, World!'

$handle->close();

// Custom threshold: spool to disk after 64 bytes
$small = IO\spool(maxMemory: 64);
$small->writeAll(Str\repeat('x', 256)); // transparently written to disk
$small->seek(0);
$small->readAll(); // 256 bytes of 'x'

$small->close();
```

## Pipes

`IO\pipe()` creates a connected pair of handles: anything written to the write end can be read from the read end.

```php
use Psl\IO;

[$reader, $writer] = IO\pipe();
$writer->writeAll('hello');
$writer->close();

$result = $reader->readAll(); // 'hello'
$reader->close();

IO\write_line('Read from pipe: %s', $result);
```

## Copying and Streaming

`IO\copy()` reads from one handle and writes to another until EOF:

```php
use Psl\IO;

$source = new IO\MemoryHandle('data to copy');
$dest = new IO\MemoryHandle();
IO\copy($source, $dest);

IO\write_line('Copied: %s', $dest->getBuffer()); // 'data to copy'
```

`IO\streaming()` multiplexes reads from several stream handles concurrently, yielding chunks as they arrive. This is useful for reading interleaved process output:

```php
use Psl\Async;
use Psl\IO;

Async\main(static function (): int {
    // Create two pipes to simulate concurrent streams
    [$outReader, $outWriter] = IO\pipe();
    [$errReader, $errWriter] = IO\pipe();

    // Write data to both pipes
    $outWriter->writeAll('stdout output');
    $outWriter->close();
    $errWriter->writeAll('stderr output');
    $errWriter->close();

    foreach (IO\streaming(['out' => $outReader, 'err' => $errReader]) as $name => $chunk) {
        IO\write_line('[%s] %s', $name, $chunk);
    }

    $outReader->close();
    $errReader->close();

    return 0;
});
```

See [src/Psl/IO/](https://github.com/php-standard-library/php-standard-library/tree/5.3.0/src/Psl/IO/) for the full API.
