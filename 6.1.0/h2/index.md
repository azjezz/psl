# H2

The `H2` component implements the HTTP/2 binary framing protocol (RFC 9113). It provides client and server connections with full support for stream multiplexing, flow control, HPACK header compression, and several HTTP/2 extensions.

## Client and Server

Connections are split into `ClientConnection` and `ServerConnection`, each implementing their role-specific interface. Both share common operations (sending headers, data, ping, goaway) via `ConnectionInterface`.

```php
use Psl\H2;
use Psl\H2\Event;
use Psl\HPACK\Header;
use Psl\Network;

// Create a socket pair for client-server communication
[$clientSocket, $serverSocket] = Network\socket_pair();

$client = new H2\ClientConnection($clientSocket);
$server = new H2\ServerConnection($serverSocket);

// Initialize both sides (sends connection preface + SETTINGS)
$client->initialize();
$server->initialize();

// Complete the handshake
$server->readClientPreface();
$server->readEvent(); // client SETTINGS
$client->readEvent(); // server SETTINGS
$client->readEvent(); // SETTINGS ACK
$server->readEvent(); // SETTINGS ACK

// Client sends a request
$streamId = $client->nextStreamId();
$client->sendHeaders(
    $streamId,
    [
        new Header(':method', 'GET'),
        new Header(':path', '/hello'),
        new Header(':scheme', 'https'),
        new Header(':authority', 'example.com'),
    ],
    endStream: true,
);

// Server reads the request
$events = $server->readEvent();
foreach ($events as $event) {
    if (!$event instanceof Event\HeadersReceived) {
        continue;
    }

    // Server sends a response
    $server->sendHeadersWithStatus($event->streamId, '200', [
        new Header('content-type', 'text/plain'),
    ]);

    $server->sendData($event->streamId, 'Hello, World!', endStream: true);
}
```

## Flow Control

`sendData()` sends a single frame and requires the caller to manage flow control windows. For large payloads, use `sendAllData()` which automatically splits data into window-sized chunks and waits for WINDOW_UPDATE frames.

```php
use Psl\H2;
use Psl\HPACK\Header;
use Psl\Str;

/** @var H2\ClientConnectionInterface $client */
/** @var H2\ServerConnectionInterface $server */

$streamId = $client->nextStreamId();
$client->sendHeaders($streamId, [
    new Header(':method', 'POST'),
    new Header(':path', '/upload'),
    new Header(':scheme', 'https'),
    new Header(':authority', 'example.com'),
]);

// sendAllData handles flow control - no manual window management needed
$largePayload = Str\repeat('data', 10_000);
$client->sendAllData($streamId, $largePayload, endStream: true);
```

## Server Push

Servers can proactively push resources via `sendPushPromise()`. Clients can reject unwanted pushes with `rejectPush()`.

```php
use Psl\H2;
use Psl\HPACK\Header;

/** @var H2\ServerConnectionInterface $server */
/** @var H2\ClientConnectionInterface $client */

// Server pushes a CSS file alongside the HTML response
$requestStreamId = 1; // client-initiated stream
$pushStreamId = $server->nextStreamId();

$server->sendPushPromise($requestStreamId, $pushStreamId, [
    new Header(':method', 'GET'),
    new Header(':path', '/style.css'),
    new Header(':scheme', 'https'),
    new Header(':authority', 'example.com'),
]);

// Send the pushed response
$server->sendHeadersWithStatus($pushStreamId, '200', [
    new Header('content-type', 'text/css'),
]);
$server->sendData($pushStreamId, 'body { color: red; }', endStream: true);

// Client can reject unwanted pushes
$client->rejectPush($pushStreamId);
```

## Extended CONNECT

RFC 8441 enables protocols like WebSocket to run over HTTP/2 streams. The client sends an extended CONNECT request with a `:protocol` pseudo-header, and after the server responds with 200, the stream becomes a bidirectional byte tunnel.

```php
use Psl\H2;
use Psl\HPACK\Header;

/** @var H2\ClientConnectionInterface $client */
/** @var H2\ServerConnectionInterface $server */

$streamId = $client->nextStreamId();
$client->sendExtendedConnect(
    $streamId,
    protocol: 'websocket',
    scheme: 'https',
    authority: 'example.com',
    path: '/chat',
    extraHeaders: [
        new Header('origin', 'https://example.com'),
        new Header('sec-websocket-version', '13'),
    ],
);

// After the server responds with 200, the stream becomes a bidirectional
// byte tunnel. Use sendData/readEvent to exchange protocol-specific frames.
```

## Alt-Svc and Origin

Servers can advertise alternative service endpoints (RFC 7838) for protocol migration, and declare authoritative origins (RFC 8336) for connection coalescing.

```php
use Psl\H2;

/** @var H2\ServerConnectionInterface $server */

// Alt-Svc (RFC 7838) advertises alternative service endpoints,
// enabling HTTP/2 to HTTP/3 migration.
$server->sendAltSvc(0, 'https://example.com', 'h3=":443"; ma=2592000');

// ORIGIN (RFC 8336) declares which origins the server is authoritative for,
// enabling connection coalescing across multiple domains.
$server->sendOrigin([
    'https://example.com',
    'https://cdn.example.com',
    'https://api.example.com',
]);
```

## Configuration

Both `ServerConfiguration` and `ClientConfiguration` provide immutable builder patterns with `with*` methods. Server configuration includes BDP auto-tuning for dynamic receive window sizing.

## Extension Frames

Beyond the core 10 frame types, the component supports:

- **ALTSVC** (0xa) -- RFC 7838 alternative service advertisement
- **ORIGIN** (0xc) -- RFC 8336 authoritative origin declaration
- **PRIORITY_UPDATE** (0x10) -- RFC 9218 extensible priority scheme

Unknown frame types are silently ignored per the RFC, ensuring forward compatibility.

See [src/Psl/H2/](https://github.com/php-standard-library/php-standard-library/tree/6.1.0/packages/h2/src/Psl/H2/) for the full API.
