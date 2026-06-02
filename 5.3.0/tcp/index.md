# TCP

The `TCP` component provides a non-blocking API for TCP client and server connections, built on top of PSL's IO and Network abstractions.

## Usage

```php
use Psl\Async;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $request = $connection->readAll();
        $connection->writeAll("echo: {$request}");
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect($address->host, $address->port ?? 0);
        $client->writeAll('hello');
        $client->shutdown();
        $response = $client->readAll();
        $client->close();
    },
]);
```

## Design

### ConnectorInterface

All TCP connectors implement `ConnectorInterface`, making them interchangeable and composable. The built-in connectors are:

- **`Connector`** -- the default connector wrapping `TCP\connect()`.
- **`RetryConnector`** -- wraps any connector with exponential backoff retry on failure.
- **`StaticConnector`** -- redirects all connections to a fixed host and port (useful for testing).

### SocketPool

`SocketPool` reuses idle TCP connections. Checked-in connections stay alive for a configurable idle timeout before being closed.

```php
use Psl\Async;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');
$address = $listener->getLocalAddress();

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        // Keep connection open until client is done
        $connection->readAll();
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($address): void {
        $pool = new TCP\SocketPool();
        $stream = $pool->checkout($address->host, $address->port ?? 0);
        // ... use stream ...
        $pool->checkin($stream);

        // Later -- reuses the same connection
        $stream = $pool->checkout($address->host, $address->port ?? 0);
        $pool->clear($stream);
        $pool->close();
    },
]);
```

### Low-Level Socket

`Socket` gives you fine-grained control over socket options before connecting or listening. Create a socket, configure it, then consume it:

```php
use Psl\Async;
use Psl\TCP\Socket;

$socket = Socket::createV4();
$socket->setReuseAddress(true);
$socket->setReusePort(true);
$socket->setNoDelay(true);
$socket->bind('127.0.0.1', 0);

$listener = $socket->listen();

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $data = $connection->readAll();
        $connection->writeAll($data);
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = \Psl\TCP\connect($address->host, $address->port ?? 0);
        $client->writeAll('test');
        $client->shutdown();
        $response = $client->readAll();
        $client->close();
    },
]);
```

## Examples

### Echo Server

```php
use Psl\Async;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');

Async\concurrently([
    'server' => static function () use ($listener): void {
        echo "Listening on {$listener->getLocalAddress()->toString()}\n";

        // Accept one connection then shut down
        $connection = $listener->accept();
        Async\run(static function () use ($connection): void {
            $data = $connection->readAll();
            $connection->writeAll($data);
            $connection->close();
        })->await();

        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect($address->host, $address->port ?? 0);
        $client->writeAll('hello from client');
        $client->shutdown();
        $response = $client->readAll();
        echo "Got: {$response}\n";
        $client->close();
    },
]);
```

### Client with Timeout

```php
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $_request = $connection->readAll();
        $connection->writeAll("HTTP/1.0 200 OK\r\n\r\nHello");
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect($address->host, $address->port ?? 0, timeout: Duration::seconds(5));
        $client->writeAll("GET / HTTP/1.0\r\nHost: localhost\r\n\r\n");
        $client->shutdown();
        $_response = $client->readAll();
        $client->close();
    },
]);
```

### Retry with Backoff

```php
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\TCP;

// Create a retry connector with exponential backoff
$connector = new TCP\RetryConnector(
    new TCP\Connector(noDelay: true),
    maxAttempts: 5,
    backoff: Duration::milliseconds(500),
);

// Demonstrate by connecting to a local server
$listener = TCP\listen('127.0.0.1');

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $connection->writeAll('connected');
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($connector, $listener): void {
        $address = $listener->getLocalAddress();
        $stream = $connector->connect($address->host, $address->port ?? 0);
        $data = $stream->readAll();
        $stream->close();
    },
]);
```

See [src/Psl/TCP/](https://github.com/php-standard-library/php-standard-library/tree/5.3.0/src/Psl/TCP/) for the full API.
