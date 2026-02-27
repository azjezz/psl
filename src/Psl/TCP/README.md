# TCP

The `TCP` component provides a non-blocking API for TCP client and server connections, built on top of PSL's IO and Network abstractions.

## Usage

```php
use Psl\Async;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1', 8080);

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $request = $connection->readAll();
        $connection->writeAll("echo: {$request}");
        $connection->close();
        $listener->close();
    },
    'client' => static function (): void {
        $client = TCP\connect('127.0.0.1', 8080);
        $client->writeAll('hello');
        $client->shutdown();
        $response = $client->readAll();
        $client->close();
    },
]);
```

## API

### Functions

---

#### `connect()`

```php
function connect(
    string $host,
    int $port,
    bool $no_delay = false,
    ?Duration $timeout = null,
): StreamInterface
```

Connect to a TCP server and return a bidirectional stream.

---

#### `listen()`

```php
function listen(
    string $host = '127.0.0.1',
    int $port = 0,
    bool $no_delay = false,
    bool $reuse_address = false,
    bool $reuse_port = false,
    int $idle_connections = 256,
): ListenerInterface
```

Create a TCP listener bound to the given address. When `$port` is `0`, the OS assigns an available port.

---

### Interfaces

---

#### `StreamInterface`

A connected TCP stream with TCP-specific socket options. Extends `Network\StreamInterface`.

**Socket Options:**

- `setNoDelay(bool $enabled): void` / `getNoDelay(): bool` — TCP_NODELAY (disable Nagle's algorithm).
- `setKeepAlive(bool $enabled): void` / `getKeepAlive(): bool` — SO_KEEPALIVE (detect dead peers on idle connections).
- `setTtl(int $ttl): void` / `getTtl(): int` — IP Time-To-Live.
- `setSendBufferSize(int $size): void` / `getSendBufferSize(): int` — SO_SNDBUF (kernel send buffer size).
- `setReceiveBufferSize(int $size): void` / `getReceiveBufferSize(): int` — SO_RCVBUF (kernel receive buffer size).
- `setLinger(?Duration $duration): void` / `getLinger(): ?Duration` — SO_LINGER (close behavior). `null` = default (graceful FIN), `Duration::zero()` = RST on close, positive duration = wait then RST.

Inherited from `Network\StreamInterface`:
- `read()`, `write()`, `readAll()`, `writeAll()`, `readFixedSize()`
- `peek()`, `shutdown()`
- `getLocalAddress()`, `getPeerAddress()`
- `close()`

---

#### `ListenerInterface`

A TCP listener that accepts incoming connections. Extends `Network\ListenerInterface`.

- `accept(): StreamInterface` — Accept the next pending TCP connection.
- `getLocalAddress(): Address` — Returns the listener's bound address.
- `close(): void` — Stop listening.

---

### Classes

---

#### `Socket`

A low-level TCP socket that can be configured before connecting or listening. Follows a create-configure-consume pattern. Requires `ext-sockets`.

**Factories:**
- `Socket::createV4(): self` — Create a new IPv4 TCP socket.
- `Socket::createV6(): self` — Create a new IPv6 TCP socket.

**Configuration (before connect/listen):**
- `bind(string $host, int $port = 0): void`
- `setReuseAddress(bool) / getReuseAddress(): bool`
- `setReusePort(bool) / getReusePort(): bool`
- `setNoDelay(bool) / getNoDelay(): bool`
- `setSendBufferSize(int) / getSendBufferSize(): int`
- `setReceiveBufferSize(int) / getReceiveBufferSize(): int`
- `setKeepAlive(bool) / getKeepAlive(): bool`
- `getLocalAddress(): Address`

**Consume (consumes the socket — cannot be reused):**
- `connect(string $host, int $port, ?Duration $timeout = null): StreamInterface`
- `listen(int $backlog = 128, int $idle_connections = 256): ListenerInterface`

---

## Examples

### Echo Server

```php
use Psl\Async;
use Psl\TCP;

Async\main(static function (): void {
    $listener = TCP\listen('0.0.0.0', 9000);
    echo "Listening on {$listener->getLocalAddress()->toString()}\n";

    while (true) {
        $connection = $listener->accept();
        Async\run(static function () use ($connection): void {
            $data = $connection->readAll();
            $connection->writeAll($data);
            $connection->close();
        });
    }
});
```

### Client with Timeout

```php
use Psl\TCP;
use Psl\DateTime\Duration;

$client = TCP\connect('example.com', 80, timeout: Duration::seconds(5));
$client->writeAll("GET / HTTP/1.0\r\nHost: example.com\r\n\r\n");
$client->shutdown();
$response = $client->readAll();
$client->close();
```

### Low-Level Socket Configuration

```php
use Psl\TCP\Socket;

$socket = Socket::createV4();
$socket->setReuseAddress(true);
$socket->setReusePort(true);
$socket->setNoDelay(true);
$socket->bind('0.0.0.0', 8080);

$listener = $socket->listen();
```

### Stream Options

```php
use Psl\TCP;
use Psl\DateTime\Duration;

$client = TCP\connect('example.com', 80);

$client->setNoDelay(true);
$client->setKeepAlive(true);
$client->setSendBufferSize(65536);
$client->setReceiveBufferSize(65536);
$client->setLinger(Duration::seconds(5));
```

---
