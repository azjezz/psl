# Socks

The `Socks` component provides a SOCKS5 proxy connector that tunnels TCP connections through a SOCKS5 proxy server (RFC 1928).

It implements `TCP\ConnectorInterface`, so it can be used anywhere a TCP connector is expected — enabling transparent proxy tunneling for any code that accepts a connector.

## Usage

```php
use Psl\Socks;

$connector = new Socks\Connector('proxy.example.com', 1080);
$stream = $connector->connect('target.example.com', 80);

$stream->writeAll("GET / HTTP/1.0\r\nHost: target.example.com\r\n\r\n");
$stream->shutdown();
$response = $stream->readAll();
$stream->close();
```

## API

### Classes

---

#### `Connector`

A TCP connector that tunnels connections through a SOCKS5 proxy. Implements `TCP\ConnectorInterface`.

```php
// Without authentication
$connector = new Socks\Connector('proxy.example.com', 1080);

// With username/password authentication (RFC 1929)
$connector = new Socks\Connector('proxy.example.com', 1080, 'user', 'pass');

// With a custom underlying connector
$connector = new Socks\Connector(
    'proxy.example.com', 1080,
    connector: new TCP\RetryConnector(new TCP\Connector()),
);
```

**Constructor:**
- `string $proxyHost` — SOCKS5 proxy hostname or IP.
- `int $proxyPort` — SOCKS5 proxy port.
- `?string $username = null` — Optional authentication username.
- `?string $password = null` — Optional authentication password.
- `TCP\ConnectorInterface $connector = new TCP\Connector()` — Connector used to reach the proxy server.

**Methods:**
- `connect(string $host, int $port, ?Duration $timeout = null): TCP\StreamInterface` — Connect to the target through the SOCKS5 proxy.

---

### Exceptions

- `Exception\SocksException` — Thrown when a SOCKS5 protocol operation fails (handshake errors, connection refused, etc.). Extends `Network\Exception\RuntimeException`.
- `Exception\AuthenticationException` — Thrown when SOCKS5 authentication fails. Extends `SocksException`.

---

## Examples

### Tunneling TLS Through a Proxy

```php
use Psl\Socks;
use Psl\TLS;

$proxy = new Socks\Connector('proxy.example.com', 1080, 'user', 'pass');
$stream = $proxy->connect('api.example.com', 443);
$tls = TLS\Connector::default()->connect($stream, 'api.example.com');

$tls->writeAll("GET /data HTTP/1.1\r\nHost: api.example.com\r\n\r\n");
```

### With Connection Pooling

```php
use Psl\Socks;
use Psl\Network;

$proxy = new Socks\Connector('proxy.example.com', 1080);
$pool = new Network\SocketPool(connector: $proxy);

$stream = $pool->checkout('api.example.com', 80);
// ... use stream ...
$pool->checkin($stream);
```

### With Retry

```php
use Psl\Socks;
use Psl\TCP;
use Psl\DateTime\Duration;

$proxy = new Socks\Connector(
    'proxy.example.com', 1080,
    connector: new TCP\RetryConnector(
        new TCP\Connector(),
        maxAttempts: 3,
        backoff: Duration::seconds(1),
    ),
);

$stream = $proxy->connect('target.example.com', 80);
```

---
