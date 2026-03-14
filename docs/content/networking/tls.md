# TLS

The `TLS` component provides a transport-agnostic API for TLS encryption. It operates on `Network\StreamInterface`, meaning it can upgrade any connected stream (TCP, Unix, or other) to a TLS-encrypted stream.

It supports TLS 1.0--1.3, ALPN protocol negotiation, SNI-based certificate selection, mutual TLS authentication, session tickets, certificate pinning, and lazy handshake inspection.

## Usage

@example('networking/tls-connect.php')

## Design

### Client Connections

The simplest path is `TLS\connect()`, which opens a TCP connection and performs the handshake in one step. For more control, use `TLS\Connector` to upgrade an existing stream:

@example('networking/tls-upgrade.php')

Configure the client with `ClientConfig`:

@example('networking/tls-client-config.php')

### Server Connections

Use `Acceptor` to perform TLS handshakes on incoming streams:

@example('networking/tls-server.php')

### SNI-Based Virtual Hosting (LazyAcceptor)

`LazyAcceptor` peeks at the TLS ClientHello before completing the handshake. This lets you inspect the client's SNI hostname and choose the appropriate `ServerConfig` dynamically:

@example('networking/tls-lazy-acceptor.php')

### TLS Connection Pooling

`TLS\TCPConnector` implements `TCP\ConnectorInterface`, which means it can be used with `TCP\SocketPool` to enable connection pooling for TLS connections. This avoids repeated TLS handshakes when making multiple requests to the same host:

@example('networking/tls-pool.php')

## Examples

### HTTPS Client with ALPN

@example('networking/tls-alpn.php')

### Certificate Pinning

@example('networking/tls-pinning.php')

### STARTTLS (Upgrade Mid-Connection)

@example('networking/tls-starttls.php')

### Inspecting Connection State

@example('networking/tls-inspect-state.php')

### Cancellation

All TLS operations that suspend (handshakes) accept a `CancellationTokenInterface`. This allows you to cancel slow or hanging handshakes:

- `Acceptor::accept($stream, $cancellation)` -- cancel server-side handshake
- `LazyAcceptor::accept($stream, $cancellation)` -- cancel ClientHello peek
- `ClientHello::complete($config, $cancellation)` -- cancel deferred handshake
- `Connector::connect($stream, $host, $cancellation)` -- cancel client-side handshake
- `TLS\connect($host, $port, $config, $cancellation)` -- cancellation propagates through both TCP connect and TLS handshake

@example('networking/tls-cancellation.php')

See `src/Psl/TLS/` for the full API.
