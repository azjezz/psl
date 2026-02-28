# Network

The `Network` component defines the shared interfaces and types used by all networking components (`TCP`, `Unix`, `TLS`, `UDP`).

It provides the abstraction layer that protocol-specific components build on top of, ensuring a consistent API across transport types.

## API

### Functions

---

#### `socket_pair()`

```php
function socket_pair(): array{Network\StreamInterface, Network\StreamInterface}
```

Create a pair of connected bidirectional stream sockets. Data written to one end can be read from the other. Useful for inter-process communication and testing.

---

### Interfaces

---

#### `SocketInterface`

Base interface for all network sockets. Extends `IO\CloseHandleInterface`.

- `getLocalAddress(): Address` — Returns the address of the local side of the socket.
- `close(): void` — Close the socket.

---

#### `StreamInterface`

A bidirectional network stream with peek and shutdown support. Extends `SocketInterface`, `IO\ReadHandleInterface`, `IO\WriteHandleInterface`, and `IO\StreamHandleInterface`.

This is the core type for connected network streams (TCP, Unix, TLS).

- `getPeerAddress(): Address` — Returns the address of the remote side of the connection.
- `peek(int $max_bytes, ?Duration $timeout = null): string` — Read up to `$max_bytes` without consuming them from the receive buffer. The data remains in the buffer and will be returned by subsequent `read()` calls.
- `shutdown(): void` — Shut down the write side of the connection. The remote peer will see EOF on their read side. Reads on this side still work.

Inherited from `IO\ReadHandleInterface`:
- `read(?int $max_bytes = null, ?Duration $timeout = null): string`
- `tryRead(?int $max_bytes = null): string`
- `readAll(?int $max_bytes = null, ?Duration $timeout = null): string`
- `readFixedSize(int $size, ?Duration $timeout = null): string`
- `reachedEndOfDataSource(): bool`

Inherited from `IO\WriteHandleInterface`:
- `write(string $bytes, ?Duration $timeout = null): int`
- `tryWrite(string $bytes): int`
- `writeAll(string $bytes, ?Duration $timeout = null): void`

---

#### `ListenerInterface`

Interface for accepting incoming connections. Extends `SocketInterface`.

- `accept(): StreamInterface` — Accept the next pending connection. Blocks until a new connection is available.
- `close(): void` — Stop listening. Open connections are not closed.

---

### Classes

---

#### `Address`

Immutable value object representing a network address.

**Properties:**
- `SocketScheme $scheme` — The socket scheme (TCP, UDP, Unix).
- `string $host` — The host address.
- `null|int $port` — Port number (0–65535), or null for Unix sockets.

**Factories:**
- `Address::create(SocketScheme $scheme, string $host, ?int $port = null): self`
- `Address::tcp(string $host = '127.0.0.1', int $port = 0): self`
- `Address::udp(string $host = '127.0.0.1', int $port = 0): self`
- `Address::unix(string $host): self`

**Methods:**
- `toString(): string` — Returns the address as a URI string (e.g. `tcp://127.0.0.1:8080`).

---

### Enums

---

#### `SocketScheme`

| Case | Value |
|------|-------|
| `Tcp` | `'tcp'` |
| `Udp` | `'udp'` |
| `Unix` | `'unix'` |

---

### Exceptions

- `Exception\ExceptionInterface` — Marker interface for all Network exceptions.
- `Exception\RuntimeException` — General runtime errors (connection failures, address retrieval failures).
- `Exception\AlreadyStoppedException` — Thrown when operating on a closed listener.
- `Exception\TimeoutException` — Thrown when an operation times out.
- `Exception\InvalidArgumentException` — Thrown when invalid arguments are provided.

---

### Interface Hierarchy

```
IO\CloseHandleInterface
└── SocketInterface
    ├── ListenerInterface
    │   ├── TCP\ListenerInterface
    │   └── Unix\ListenerInterface
    └── StreamInterface  (+ IO\ReadHandleInterface, IO\WriteHandleInterface, IO\StreamHandleInterface)
        ├── TCP\StreamInterface
        ├── Unix\StreamInterface
        └── TLS\StreamInterface
```

---
