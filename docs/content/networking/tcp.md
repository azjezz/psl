# TCP

The `TCP` component provides a non-blocking API for TCP client and server connections, built on top of PSL's IO and Network abstractions.

## Usage

@example('networking/tcp-echo.php')

## Design

### ConnectorInterface

All TCP connectors implement `ConnectorInterface`, making them interchangeable and composable. The built-in connectors are:

- **`Connector`** -- the default connector wrapping `TCP\connect()`.
- **`RetryConnector`** -- wraps any connector with exponential backoff retry on failure.
- **`StaticConnector`** -- redirects all connections to a fixed host and port (useful for testing).

### SocketPool

`SocketPool` reuses idle TCP connections. Checked-in connections stay alive for a configurable idle timeout before being closed.

@example('networking/tcp-socket-pool.php')

### Low-Level Socket

`Socket` gives you fine-grained control over socket options before connecting or listening. Create a socket, configure it, then consume it:

@example('networking/tcp-low-level-socket.php')

## Examples

### Echo Server

@example('networking/tcp-echo-server.php')

### Client with Timeout

@example('networking/tcp-client-timeout.php')

### Retry with Backoff

@example('networking/tcp-retry-backoff.php')

See `src/Psl/TCP/` for the full API.
