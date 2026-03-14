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

## Quoted-Printable

Quoted-printable encoding represents 8-bit data using only printable ASCII characters, as defined by RFC 2045 §6.7. It is commonly used in email (MIME) to encode content that is mostly ASCII with occasional special characters. Unlike Base64, quoted-printable keeps readable text readable.

@example('text/encoding-quoted-printable.php')

Both `encode()` and `encode_line()` accept optional `$max_line_length` (default 76) and `$line_ending` (default `"\r\n"`) parameters.

## Encoded-Word (RFC 2047)

Encoded-word encoding is used in MIME headers (Subject, From, etc.) to represent non-ASCII text. `encode()` automatically selects Q-encoding (for mostly ASCII text) or B-encoding (for mostly non-ASCII text) based on the proportion of non-printable bytes.

@example('text/encoding-encoded-word.php')

`encode()` accepts an optional `$charset` parameter of type `Psl\Str\Encoding` (default `Encoding::Utf8`). `decode()` handles charset conversion automatically, and per RFC 2047 §6.2, whitespace between adjacent encoded-words is removed.

See `src/Psl/Encoding/` for the full API.
