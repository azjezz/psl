# Encoding

The `Encoding` component provides functions for encoding and decoding binary data to and from text-safe representations. It supports Base64 (with multiple variants) and hexadecimal encoding, making it straightforward to prepare binary data for transport over text-based protocols like HTTP, email, or JSON.

## Base64

### Encoding and Decoding

@example('text/encoding-base64.php')

### URL-Safe Variant

Standard Base64 uses `+` and `/`, which conflict with URLs. The `UrlSafe` variant replaces them with `-` and `_`:

@example('text/encoding-base64-urlsafe.php')

The `Variant` enum supports `Standard`, `UrlSafe`, `DotSlash`, and `DotSlashOrdered` for specialized use cases.

### Padding Control

Both `encode()` and `decode()` accept a padding parameter. You can strip padding on encode and decode without it:

@example('text/encoding-base64-padding.php')

### Error Handling

Decoding invalid input throws typed exceptions rather than returning `false`:

@example('text/encoding-base64-error.php')

## Hex

Hexadecimal encoding converts each byte into two hex characters. This is useful for displaying binary data like hashes, checksums, or cryptographic keys in a human-readable form.

@example('text/encoding-hex.php')

See `src/Psl/Encoding/` for the full API.
