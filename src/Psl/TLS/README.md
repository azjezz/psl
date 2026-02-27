# TLS

The `TLS` component provides a transport-agnostic API for TLS encryption. It operates on `Network\StreamInterface`, meaning it can upgrade any connected stream (TCP, Unix, or other) to a TLS-encrypted stream.

It supports TLS 1.0–1.3, ALPN protocol negotiation, SNI-based certificate selection, mutual TLS authentication, session tickets, and lazy handshake inspection.

## Usage

```php
use Psl\TCP;
use Psl\TLS;

// Client: connect to an HTTPS server
$stream = TCP\connect('example.com', 443);
$tls = TLS\Connector::default()->connect($stream, 'example.com');

$tls->writeAll("GET / HTTP/1.0\r\nHost: example.com\r\n\r\n");
$tls->shutdown();
$response = $tls->readAll();
$tls->close();
```

## API

### Interfaces

---

#### `StreamInterface`

A TLS-encrypted network stream. Extends `Network\StreamInterface`.

- `getState(): ConnectionState` — Returns the TLS connection state (negotiated version, cipher, ALPN, peer certificates).

All read/write/peek/shutdown/address methods are inherited from `Network\StreamInterface`.

---

### Classes

---

#### `Connector`

Performs TLS client handshakes on existing streams. Implements `DefaultInterface`.

- `static default(): static` — Create a connector with the default `ClientConfig`.
- `connect(Network\StreamInterface $stream, ?string $server_name = null): StreamInterface` — Perform a TLS handshake on the given stream. `$server_name` overrides the SNI hostname if the config's `peerName` is null.

```php
// With default config
$tls = TLS\Connector::default()->connect($stream, 'example.com');

// With custom config
$connector = new TLS\Connector(
    TLS\ClientConfig::default()
        ->withAlpnProtocols(['h2', 'http/1.1'])
        ->withMinimumVersion(TLS\Version::Tls12)
);
$tls = $connector->connect($stream, 'example.com');
```

---

#### `Acceptor`

Performs TLS server handshakes on incoming streams.

- `accept(Network\StreamInterface $stream): StreamInterface` — Perform a TLS handshake on an incoming stream.

```php
$cert = TLS\Certificate::create('/path/to/cert.pem', '/path/to/key.pem');
$acceptor = new TLS\Acceptor(TLS\ServerConfig::create($cert));

while (true) {
    $stream = $listener->accept();
    $tls = $acceptor->accept($stream);
    // ... handle encrypted connection
}
```

---

#### `LazyAcceptor`

Peeks at the TLS ClientHello before completing the handshake. This allows inspecting the client's SNI hostname and ALPN protocols to choose the appropriate `ServerConfig` dynamically. Implements `DefaultInterface`.

- `static default(): static` — Create a new lazy acceptor.
- `accept(Network\StreamInterface $stream): ClientHello` — Peek at the ClientHello and return an object to complete the handshake.

```php
$lazy = TLS\LazyAcceptor::default();

while (true) {
    $stream = $listener->accept();
    $hello = $lazy->accept($stream);

    $config = match ($hello->getServerName()) {
        'api.example.com' => $apiConfig,
        'www.example.com' => $wwwConfig,
        default => $defaultConfig,
    };

    $tls = $hello->complete($config);
    // ... handle encrypted connection
}
```

---

#### `ClientHello`

Represents a parsed TLS ClientHello. Obtained from `LazyAcceptor::accept()`.

- `getServerName(): ?string` — The SNI hostname the client requested.
- `getAlpnProtocols(): ?list<string>` — The ALPN protocols the client advertised.
- `complete(ServerConfig $config): StreamInterface` — Complete the TLS handshake with the chosen configuration.

---

#### `ClientConfig`

Immutable TLS configuration for client connections. Implements `DefaultInterface`. All `with*()` methods return a new instance.

**Properties:**
- `?string $peerName` — SNI hostname for the handshake.
- `bool $peerVerification` — Whether to verify the peer certificate (default: `true`).
- `bool $allowSelfSigned` — Whether to allow self-signed certificates (default: `false`).
- `?string $certificateAuthority` — Path to a CA file for peer verification.
- `?string $certificateAuthorityPath` — Path to a CA directory.
- `?Certificate $certificate` — Client certificate for mutual TLS.
- `?Version $minimumVersion` — Minimum TLS protocol version.
- `?Version $maximumVersion` — Maximum TLS protocol version.
- `?string $ciphers` — OpenSSL cipher string.
- `int $securityLevel` — OpenSSL security level (0–5, default: `2`).
- `?list<string> $alpnProtocols` — ALPN protocol list (e.g. `['h2', 'http/1.1']`).
- `bool $sessionTickets` — Enable TLS session tickets (default: `true`).

**Methods:**
- `static default(): static`
- `withPeerName(?string): self`
- `withPeerVerification(bool): self`
- `withAllowSelfSigned(bool): self`
- `withCertificateAuthority(?string): self`
- `withCertificateAuthorityPath(?string): self`
- `withCertificate(?Certificate): self`
- `withMinimumVersion(?Version): self`
- `withMaximumVersion(?Version): self`
- `withCiphers(?string): self`
- `withSecurityLevel(int): self`
- `withAlpnProtocols(?list<string>): self`
- `withSessionTickets(bool): self`

---

#### `ServerConfig`

Immutable TLS configuration for servers. All `with*()` methods return a new instance.

**Properties:**
- `Certificate $certificate` — Server certificate (required).
- `?Version $minimumVersion` — Minimum TLS protocol version.
- `?Version $maximumVersion` — Maximum TLS protocol version.
- `?string $ciphers` — OpenSSL cipher string.
- `int $securityLevel` — OpenSSL security level (0–5, default: `2`).
- `?string $certificateAuthority` — Path to CA file for client certificate verification.
- `?string $certificateAuthorityPath` — Path to CA directory for client certificate verification.
- `?list<string> $alpnProtocols` — ALPN protocol list.
- `array<string, Certificate> $sniCertificates` — SNI hostname-to-certificate mapping.
- `bool $sessionTickets` — Enable TLS session tickets (default: `true`).

**Methods:**
- `static create(Certificate $certificate): self`
- `withCertificate(Certificate): self`
- `withMinimumVersion(?Version): self`
- `withMaximumVersion(?Version): self`
- `withCiphers(?string): self`
- `withSecurityLevel(int): self`
- `withCertificateAuthority(?string): self`
- `withCertificateAuthorityPath(?string): self`
- `withAlpnProtocols(?list<string>): self`
- `withSniCertificate(string $hostname, Certificate $certificate): self`
- `withSniCertificates(array<string, Certificate>): self`
- `withSessionTickets(bool): self`

---

#### `Certificate`

Immutable TLS certificate configuration.

**Properties:**
- `string $certificateFile` — Path to the certificate file (PEM format).
- `string $keyFile` — Path to the private key file (PEM format).
- `?string $passphrase` — Optional passphrase for the private key.

**Factories:**
- `Certificate::create(string $certificate_file, string $key_file, ?string $passphrase = null): self`

---

#### `ConnectionState`

Immutable snapshot of TLS connection state captured after the handshake.

**Properties:**
- `Version $version` — Negotiated TLS protocol version.
- `string $cipherName` — Name of the negotiated cipher (e.g. `"TLS_AES_256_GCM_SHA384"`).
- `int $cipherBits` — Cipher strength in bits (e.g. `256`).
- `string $cipherVersion` — Protocol version string of the cipher.
- `?string $alpnProtocol` — Negotiated ALPN protocol, or null if ALPN was not used.
- `?PeerCertificate $peerCertificate` — The peer's certificate, or null if not available.
- `?list<PeerCertificate> $peerCertificateChain` — The peer's certificate chain, or null if not available.

---

#### `PeerCertificate`

Immutable representation of a peer's X.509 certificate. Wraps parsed certificate data without exposing OpenSSL extension types.

**Properties:**
- `string $subject` — Certificate subject CN.
- `string $issuer` — Certificate issuer CN.
- `string $serialNumber` — Certificate serial number (hex).
- `DateTime\Timestamp $validFrom` — Certificate validity start.
- `DateTime\Timestamp $validTo` — Certificate validity end.
- `string $fingerprint` — SHA-256 fingerprint.

---

### Enums

---

#### `Version`

TLS protocol versions. Implements `DefaultInterface`.

| Case | Value |
|------|-------|
| `Tls10` | `0` |
| `Tls11` | `1` |
| `Tls12` | `2` |
| `Tls13` | `3` |

- `static default(): static` — Returns `Version::Tls13`.

---

### Exceptions

- `Exception\HandshakeFailedException` — Thrown when a TLS handshake fails. Extends `Network\Exception\RuntimeException`.

---

## Examples

### HTTPS Client

```php
use Psl\TCP;
use Psl\TLS;

$stream = TCP\connect('example.com', 443);
$tls = TLS\Connector::default()->connect($stream, 'example.com');

$tls->writeAll("GET / HTTP/1.0\r\nHost: example.com\r\n\r\n");
$tls->shutdown();
echo $tls->readAll();
$tls->close();
```

### HTTPS Client with ALPN

```php
use Psl\TCP;
use Psl\TLS;

$connector = new TLS\Connector(
    TLS\ClientConfig::default()
        ->withAlpnProtocols(['h2', 'http/1.1']),
);

$stream = TCP\connect('example.com', 443);
$tls = $connector->connect($stream, 'example.com');

$protocol = $tls->getState()->alpnProtocol; // 'h2'
```

### TLS Server

```php
use Psl\Async;
use Psl\TCP;
use Psl\TLS;

$cert = TLS\Certificate::create('/path/to/cert.pem', '/path/to/key.pem');
$acceptor = new TLS\Acceptor(
    TLS\ServerConfig::create($cert)
        ->withAlpnProtocols(['h2', 'http/1.1']),
);

$listener = TCP\listen('0.0.0.0', 443);

while (true) {
    $stream = $listener->accept();
    Async\run(static function () use ($stream, $acceptor): void {
        $tls = $acceptor->accept($stream);
        // ... handle encrypted connection
        $tls->close();
    });
}
```

### SNI-Based Virtual Hosting

```php
use Psl\TCP;
use Psl\TLS;

$lazy = TLS\LazyAcceptor::default();
$listener = TCP\listen('0.0.0.0', 443);

$configs = [
    'api.example.com' => TLS\ServerConfig::create(
        TLS\Certificate::create('/certs/api.pem', '/certs/api.key'),
    ),
    'www.example.com' => TLS\ServerConfig::create(
        TLS\Certificate::create('/certs/www.pem', '/certs/www.key'),
    ),
];

$default = TLS\ServerConfig::create(
    TLS\Certificate::create('/certs/default.pem', '/certs/default.key'),
);

while (true) {
    $stream = $listener->accept();
    $hello = $lazy->accept($stream);
    $config = $configs[$hello->getServerName()] ?? $default;
    $tls = $hello->complete($config);
    // ... handle connection
}
```

### STARTTLS (Upgrade Mid-Connection)

```php
use Psl\TCP;
use Psl\TLS;
use Psl\IO;

$listener = TCP\listen('0.0.0.0', 25);
$cert = TLS\Certificate::create('/certs/mail.pem', '/certs/mail.key');
$acceptor = new TLS\Acceptor(TLS\ServerConfig::create($cert));

$stream = $listener->accept();

// Plaintext phase
$reader = new IO\Reader($stream);
$line = $reader->readLine(); // "EHLO client.example.com"
$stream->writeAll("250-mail.example.com\r\n250 STARTTLS\r\n");

$line = $reader->readLine(); // "STARTTLS"
$stream->writeAll("220 Ready to start TLS\r\n");

// Upgrade to TLS
$tls = $acceptor->accept($stream);
// ... continue with encrypted SMTP
```

### Inspecting Connection State

```php
use Psl\TCP;
use Psl\TLS;

$stream = TCP\connect('example.com', 443);
$tls = TLS\Connector::default()->connect($stream, 'example.com');

$state = $tls->getState();
echo "TLS {$state->version->name}\n";         // "TLS Tls13"
echo "Cipher: {$state->cipherName}\n";         // "TLS_AES_256_GCM_SHA384"
echo "Bits: {$state->cipherBits}\n";           // 256
echo "ALPN: {$state->alpnProtocol}\n";         // "h2" or null

if ($state->peerCertificate !== null) {
    echo "Subject: {$state->peerCertificate->subject}\n";
    echo "Issuer: {$state->peerCertificate->issuer}\n";
    echo "Valid until: {$state->peerCertificate->validTo}\n";
}

$tls->close();
```

---
