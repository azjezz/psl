# Unix

The `Unix` component provides a non-blocking API for Unix domain socket connections, built on top of PSL's IO and Network abstractions.

Unix domain sockets provide local inter-process communication without the overhead of TCP's network stack. Not available on Windows.

## Usage

@example('networking/unix-echo.php')

## Configuration

`Unix\listen()` accepts a `ListenConfiguration` to control backlog (default 512) and idle connections (default 256).

## Examples

### Echo Server

@example('networking/unix-echo-server.php')

### Client with Cancellation

@example('networking/unix-client-timeout.php')

### Concurrent Connections

@example('networking/unix-concurrent.php')

See `src/Psl/Unix/` for the full API.
