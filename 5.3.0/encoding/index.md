# Encoding

The `Encoding` component provides functions for encoding and decoding binary data to and from text-safe representations. It supports Base64 (with multiple variants) and hexadecimal encoding, making it straightforward to prepare binary data for transport over text-based protocols like HTTP, email, or JSON.

## Base64

### Encoding and Decoding

```php
use Psl\Encoding\Base64;

$encoded = Base64\encode('Hello, World!');
// 'SGVsbG8sIFdvcmxkIQ=='

$decoded = Base64\decode($encoded);

// 'Hello, World!'
```

### URL-Safe Variant

Standard Base64 uses `+` and `/`, which conflict with URLs. The `UrlSafe` variant replaces them with `-` and `_`:

```php
use Psl\Encoding\Base64;
use Psl\SecureRandom;

// Standard encoding may produce URL-unfriendly characters
$binaryToken = SecureRandom\bytes(8);
$token = Base64\encode($binaryToken);

// URL-safe encoding is better for query parameters, filenames, and tokens
$token = Base64\encode($binaryToken, Base64\Variant::UrlSafe);
$original = Base64\decode($token, Base64\Variant::UrlSafe);
```

The `Variant` enum supports `Standard`, `UrlSafe`, `DotSlash`, and `DotSlashOrdered` for specialized use cases.

### Padding Control

Both `encode()` and `decode()` accept a padding parameter. You can strip padding on encode and decode without it:

```php
use Psl\Encoding\Base64;

$encoded = Base64\encode('Hello!', padding: false);
// 'SGVsbG8h' (no trailing '=')

$decoded = Base64\decode($encoded, explicit_padding: false);

// 'Hello!'
```

### Error Handling

Decoding invalid input throws typed exceptions rather than returning `false`:

```php
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;

try {
    Base64\decode('not-valid-base64!');
} catch (Exception\RangeException $e) {
    echo $e->getMessage() . "\n";
}
```

## Hex

Hexadecimal encoding converts each byte into two hex characters. This is useful for displaying binary data like hashes, checksums, or cryptographic keys in a human-readable form.

```php
use Psl\Encoding\Exception;
use Psl\Encoding\Hex;

$hex = Hex\encode('Hello');
// '48656c6c6f'

$binary = Hex\decode('48656c6c6f');
// 'Hello'

// Invalid hex throws immediately
try {
    Hex\decode('xyz');
} catch (Exception\RangeException $e) {
    echo $e->getMessage() . "\n";
}

// Odd-length strings are rejected
try {
    Hex\decode('abc');
} catch (Exception\RangeException $e) {
    echo $e->getMessage() . "\n";
}
```

See [src/Psl/Encoding/](https://github.com/php-standard-library/php-standard-library/tree/5.3.0/src/Psl/Encoding/) for the full API.
