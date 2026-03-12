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

@example('io/io-write.php')

All output functions support `sprintf`-style formatting.

## Standard Handles

Three functions return the process-level I/O handles:

@example('io/io-standard-handles.php')

In non-CLI SAPIs, `input_handle()` reads from `php://input` and `output_handle()` writes to `php://output`. `error_handle()` returns `null` outside CLI.

## MemoryHandle

`MemoryHandle` is an in-memory buffer implementing read, write, seek, and close. It is useful for testing code that accepts handle interfaces without touching the filesystem or network.

@example('io/io-memory-handle.php')

## Reader

`Reader` wraps any `ReadHandleInterface` with buffered, higher-level reading methods:

@example('io/io-reader.php')

### Bounded Reads

`Reader::readUntilBounded()` works like `readUntil()` but enforces a maximum byte limit. If the suffix is not found within `$max_bytes`, an `IO\Exception\OverflowException` is thrown. This prevents unbounded memory consumption when reading from untrusted sources — for example, capping HTTP header lines to a safe size so a malicious client cannot exhaust memory by sending an endless line.

@example('io/io-reader-bounded.php')

## Spool

`IO\spool()` creates a handle that writes to memory until a threshold is reached (default 2MB), then transparently spools to a temporary file on disk. This is useful when buffering data of unknown size without risking excessive memory usage.

@example('io/io-spool.php')

## Pipes

`IO\pipe()` creates a connected pair of handles: anything written to the write end can be read from the read end.

@example('io/io-pipe.php')

## Copying and Streaming

`IO\copy()` reads from one handle and writes to another until EOF:

@example('io/io-copy.php')

`IO\streaming()` multiplexes reads from several stream handles concurrently, yielding chunks as they arrive. This is useful for reading interleaved process output:

@example('io/io-streaming.php')

See `src/Psl/IO/` for the full API.
