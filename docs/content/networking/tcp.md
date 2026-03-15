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

### Configuration

`TCP\listen()` and `TCP\connect()` accept configuration objects that control socket behavior:

- **`ListenConfiguration`** -- noDelay, reuseAddress, reusePort, backlog (default 512), idleConnections (default 256)
- **`ConnectConfiguration`** -- noDelay

All configuration objects are immutable and provide `with*` builder methods for fluent configuration:

@example('networking/tcp-configuration.php')

@example('networking/tcp-backlog.php')

### Low-Level Socket

`Socket` gives you fine-grained control over socket creation. Create a socket, bind to an address, then pass a configuration to `listen()` or `connect()`:

@example('networking/tcp-low-level-socket.php')

## Examples

### Echo Server

@example('networking/tcp-echo-server.php')

### Client with Cancellation

@example('networking/tcp-client-timeout.php')

### Retry with Backoff

@example('networking/tcp-retry-backoff.php')

### Cancellable Accept

`ListenerInterface::accept()` accepts a `CancellationTokenInterface`, allowing you to cancel waiting for connections, for example during a graceful shutdown:

@example('networking/tcp-cancellable-accept.php')

### Restricted Listener

`RestrictedListener` wraps any `ListenerInterface` and restricts connections to a set of allowed `IP\Address` and `CIDR\Block` entries. Rejected connections are closed silently.

@example('networking/tcp-restricted-listener.php')

See `src/Psl/TCP/` for the full API.
