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

#### `ConnectorInterface`

A pluggable interface for establishing TCP connections. Accepts a host, port, and optional timeout, and returns a connected stream.

All connectors in this component implement this interface, making them interchangeable and composable.

```php
interface ConnectorInterface
{
    public function connect(string $host, int $port, ?Duration $timeout = null): StreamInterface;
}
```

---

#### `StreamInterface`

A connected TCP stream. Extends `Network\StreamInterface`.

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

#### `Connector`

Default TCP connector. Wraps `TCP\connect()` and implements both `ConnectorInterface` and `DefaultInterface`.

```php
$connector = TCP\Connector::default();
$stream = $connector->connect('example.com', 80);
```

**Constructor:**
- `bool $noDelay = false` — Enable TCP_NODELAY (disable Nagle's algorithm).

---

#### `RetryConnector`

Wraps any `ConnectorInterface` with exponential backoff retry on connection failure.

```php
$connector = new TCP\RetryConnector(
    new TCP\Connector(),
    maxAttempts: 3,
    backoff: Duration::milliseconds(200),
    backoffMultiplier: 2,
);

$stream = $connector->connect('example.com', 80);
```

**Constructor:**
- `ConnectorInterface $connector` — The underlying connector.
- `int $maxAttempts = 3` — Maximum attempts (1–10).
- `?Duration $backoff = null` — Base delay before first retry (default: 100ms).
- `int $backoffMultiplier = 2` — Exponential multiplier per retry.

---

#### `StaticConnector`

Redirects all connections to a fixed host and port, ignoring the host/port passed to `connect()`. Useful for testing.

```php
$connector = new TCP\StaticConnector('127.0.0.1', 3306);

// Actually connects to 127.0.0.1:3306 regardless of arguments
$stream = $connector->connect('db.production.internal', 5432);
```

**Constructor:**
- `string $host` — Fixed host to connect to.
- `int $port` — Fixed port to connect to.
- `ConnectorInterface $connector = new Connector()` — Underlying connector.

---

#### `Socket`

A low-level TCP socket that can be configured before connecting or listening. Create a socket, configure options, then consume it by calling `connect()` or `listen()`.

**Factories:**
- `Socket::createV4(): self` — Create a new IPv4 TCP socket.
- `Socket::createV6(): self` — Create a new IPv6 TCP socket.

**Configuration (before connect/listen):**
- `bind(string $host, int $port = 0): void`
- `setReuseAddress(bool) / getReuseAddress(): bool`
- `setReusePort(bool) / getReusePort(): bool`
- `setNoDelay(bool) / getNoDelay(): bool`
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

### Retry with Backoff

```php
use Psl\TCP;
use Psl\DateTime\Duration;

$connector = new TCP\RetryConnector(
    new TCP\Connector(noDelay: true),
    maxAttempts: 5,
    backoff: Duration::milliseconds(500),
);

$stream = $connector->connect('flaky-service.internal', 8080);
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

---
