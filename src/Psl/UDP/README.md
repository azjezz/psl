# UDP

The `UDP` component provides a non-blocking API for sending and receiving datagrams over UDP.

It supports both connected and unconnected modes, multicast groups (IPv4 and IPv6), broadcast, peek, and configurable socket options.

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

### Classes

---

#### `Socket`

A UDP socket for sending and receiving datagrams. Implements `IO\CloseHandleInterface` and `IO\StreamHandleInterface`.

Supports two modes of operation:

- **Unconnected mode** (default): Use `sendTo()` / `receiveFrom()` to send and receive datagrams to/from arbitrary addresses.
- **Connected mode** (after calling `connect()`): Use `send()` / `receive()` to communicate with a single peer. Mixing modes throws an exception.

**Factory:**

- `static bind(string $host = '0.0.0.0', int $port = 0, bool $reuse_address = false, bool $reuse_port = false, bool $broadcast = false): self` — Create a UDP socket bound to the given address.

**Connection:**

- `connect(string $host, int $port): void` — Connect to a remote address, switching to connected mode.

**Unconnected Mode:**

- `sendTo(string $data, Address $address, ?Duration $timeout = null): int` — Send a datagram to a specific address.
- `receiveFrom(int $max_bytes, ?Duration $timeout = null): array{string, Address}` — Receive a datagram and the sender's address.
- `peekFrom(int $max_bytes, ?Duration $timeout = null): array{string, Address}` — Peek at an incoming datagram with sender address, without consuming it.

**Connected Mode:**

- `send(string $data, ?Duration $timeout = null): int` — Send a datagram on the connected socket.
- `receive(int $max_bytes, ?Duration $timeout = null): string` — Receive a datagram on the connected socket.
- `peek(int $max_bytes, ?Duration $timeout = null): string` — Peek at an incoming datagram without consuming it.

**Addresses:**

- `getLocalAddress(): Address` — Get the local bound address.
- `getPeerAddress(): ?Address` — Get the connected peer address, or null if unconnected.

**Socket Options:**

- `setBroadcast(bool $enabled): void` / `getBroadcast(): bool` — SO_BROADCAST.
- `setTtl(int $ttl): void` / `getTtl(): int` — IP Time-To-Live.

**Multicast (IPv4):**

- `joinMulticastV4(string $multicast_address, string $interface_address): void`
- `leaveMulticastV4(string $multicast_address, string $interface_address): void`

**Multicast (IPv6):**

- `joinMulticastV6(string $multicast_address, int $interface_index): void`
- `leaveMulticastV6(string $multicast_address, int $interface_index): void`

**Lifecycle:**

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

$socket = UDP\Socket::bind('127.0.0.1', 0);
$socket->connect('8.8.8.8', 53);

$socket->send($dns_query);
$response = $socket->receive(512);

$socket->close();
```

### Multicast

```php
use Psl\UDP;

$socket = UDP\Socket::bind('0.0.0.0', 5000, reuse_address: true);
$socket->joinMulticastV4('224.0.0.1', '0.0.0.0');

[$data, $sender] = $socket->receiveFrom(1024);

$socket->leaveMulticastV4('224.0.0.1', '0.0.0.0');
$socket->close();
```

### Broadcast

```php
use Psl\Network\Address;
use Psl\UDP;

$socket = UDP\Socket::bind('0.0.0.0', 0, broadcast: true);
$socket->sendTo('discovery', Address::udp('255.255.255.255', 5000));
$socket->close();
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
