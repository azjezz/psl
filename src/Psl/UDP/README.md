# UDP

The `UDP` component provides a non-blocking API for sending and receiving datagrams over UDP.

It uses two distinct types for type-safe socket usage:

- **`Socket`** — An unconnected socket for sending/receiving to arbitrary addresses.
- **`ConnectedSocket`** — A connected socket for communicating with a single peer.

## Usage

```php
use Psl\Async;
use Psl\Network\Address;
use Psl\UDP;

$server = UDP\Socket::bind('127.0.0.1', 9999);

Async\concurrently([
    'server' => static function () use ($server): void {
        [$data, $sender] = $server->receiveFrom(1024);
        $server->sendTo("echo: {$data}", $sender);
        $server->close();
    },
    'client' => static function (): void {
        $client = UDP\Socket::bind('127.0.0.1', 0);
        $client->sendTo('hello', Address::udp('127.0.0.1', 9999));
        [$response, $_] = $client->receiveFrom(1024);
        $client->close();
    },
]);
```

## API

### Functions

---

#### `connect(string $host, int $port): ConnectedSocket`

Create a connected UDP socket in one step. Binds to a local address and connects to the given host and port.

```php
$socket = UDP\connect('8.8.8.8', 53);
$socket->send($dns_query);
$response = $socket->receive(512);
$socket->close();
```

### Classes

---

#### `Socket`

An unconnected UDP socket. Implements `Network\SocketInterface` and `IO\StreamHandleInterface`.

**Factory:**

- `static bind(string $host = '0.0.0.0', int $port = 0, bool $reuse_address = false, bool $reuse_port = false, bool $broadcast = false): self` — Create a UDP socket bound to the given address.

**Sending & Receiving:**

- `sendTo(string $data, Address $address, ?Duration $timeout = null): int` — Send a datagram to a specific address.
- `receiveFrom(int $max_bytes, ?Duration $timeout = null): array{string, Address}` — Receive a datagram and the sender's address.
- `peekFrom(int $max_bytes, ?Duration $timeout = null): array{string, Address}` — Peek at an incoming datagram with sender address, without consuming it.

**Connecting:**

- `connect(string $host, int $port): ConnectedSocket` — Connect to a remote address, returning a `ConnectedSocket`. This socket is closed after connecting.

**Lifecycle:**

- `getLocalAddress(): Address` — Get the local bound address.
- `getStream(): resource|object|null` — Access the underlying stream resource.
- `close(): void` — Close the socket.

---

#### `ConnectedSocket`

A connected UDP socket for communicating with a single peer. Obtained via `Socket::connect()` or `connect()`.

Implements `Network\SocketInterface` and `IO\StreamHandleInterface`.

**Sending & Receiving:**

- `send(string $data, ?Duration $timeout = null): int` — Send a datagram to the connected peer.
- `receive(int $max_bytes, ?Duration $timeout = null): string` — Receive a datagram from the connected peer.
- `peek(int $max_bytes, ?Duration $timeout = null): string` — Peek at an incoming datagram without consuming it.

**Lifecycle:**

- `getLocalAddress(): Address` — Get the local bound address.
- `getPeerAddress(): Address` — Get the connected peer address.
- `getStream(): resource|object|null` — Access the underlying stream resource.
- `close(): void` — Close the socket.

---

## Examples

### Echo Server

```php
use Psl\Async;
use Psl\UDP;

Async\main(static function (): void {
    $socket = UDP\Socket::bind('0.0.0.0', 9999);
    echo "Listening on {$socket->getLocalAddress()->toString()}\n";

    while (true) {
        [$data, $sender] = $socket->receiveFrom(65507);
        $socket->sendTo($data, $sender);
    }
});
```

### Connected Mode

```php
use Psl\UDP;

$socket = UDP\connect('8.8.8.8', 53);

$socket->send($dns_query);
$response = $socket->receive(512);

$socket->close();
```

### Type-Safe Transition

```php
use Psl\UDP;

// Start with an unconnected socket
$socket = UDP\Socket::bind('127.0.0.1', 0);

// Connect returns a ConnectedSocket — the original socket is closed
$connected = $socket->connect('8.8.8.8', 53);

// $socket is now closed — only $connected is usable
$connected->send($data);
$response = $connected->receive(512);
$connected->close();
```

### Peek

```php
use Psl\UDP;

$socket = UDP\Socket::bind('127.0.0.1', 9999);

// Peek at data without consuming it
[$data, $sender] = $socket->peekFrom(1024);
// Same data is still available for receiveFrom()
[$data, $sender] = $socket->receiveFrom(1024);

$socket->close();
```

---
