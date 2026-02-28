# Unix

The `Unix` component provides a non-blocking API for Unix domain socket connections, built on top of PSL's IO and Network abstractions.

Unix domain sockets provide local inter-process communication without the overhead of TCP's network stack. Not available on Windows.

## Usage

```php
use Psl\Async;
use Psl\Unix;

$listener = Unix\listen('/tmp/my-app.sock');

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $data = $connection->readAll();
        $connection->writeAll("echo: {$data}");
        $connection->close();
        $listener->close();
    },
    'client' => static function (): void {
        $client = Unix\connect('/tmp/my-app.sock');
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
function connect(string $path, ?Duration $timeout = null): StreamInterface
```

Connect to a Unix domain socket at the given path.

---

#### `listen()`

```php
function listen(string $path, int $idle_connections = 256): ListenerInterface
```

Create a Unix domain socket listener bound to the given filesystem path.

---

### Interfaces

---

#### `StreamInterface`

A connected Unix domain socket stream. Extends `Network\StreamInterface`.

No additional methods beyond what `Network\StreamInterface` provides:
- `read()`, `write()`, `readAll()`, `writeAll()`, `readFixedSize()`
- `peek()`, `shutdown()`
- `getLocalAddress()`, `getPeerAddress()`
- `close()`

---

#### `ListenerInterface`

A Unix domain socket listener. Extends `Network\ListenerInterface`.

- `accept(): StreamInterface` — Accept the next pending Unix connection.
- `getLocalAddress(): Address` — Returns the listener's bound address.
- `close(): void` — Stop listening.

---

### Classes

---

#### `Socket`

A low-level Unix domain socket that can be configured before connecting or listening.

**Factory:**
- `static create(): self` — Create a new Unix domain socket.

**Methods:**
- `bind(string $path): void` — Bind the socket to a filesystem path.
- `connect(string $path, ?Duration $timeout = null): StreamInterface` — Connect and return a stream. Consumes the socket.
- `listen(int $backlog = 128, int $idle_connections = 256): ListenerInterface` — Start listening. Consumes the socket.
- `getLocalAddress(): Address` — Get the local address the socket is bound to.

---

## Examples

### Echo Server

```php
use Psl\Async;
use Psl\Unix;

Async\main(static function (): void {
    $path = '/tmp/echo.sock';
    $listener = Unix\listen($path);
    echo "Listening on {$path}\n";

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
use Psl\Unix;
use Psl\DateTime\Duration;

$client = Unix\connect('/tmp/my-app.sock', Duration::seconds(5));
$client->writeAll('ping');
$client->shutdown();
$response = $client->readAll();
$client->close();
```

### Concurrent Connections

```php
use Psl\Async;
use Psl\Unix;

$listener = Unix\listen('/tmp/my-app.sock');

// Accept multiple clients concurrently
$server = Async\run(static function () use ($listener): void {
    while (true) {
        $connection = $listener->accept();
        Async\run(static function () use ($connection): void {
            $request = $connection->readAll();
            $connection->writeAll("handled: {$request}");
            $connection->close();
        });
    }
});
```

---
