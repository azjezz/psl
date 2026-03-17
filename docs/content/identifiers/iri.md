# IRI

The `IRI` component provides RFC 3987 compliant Internationalized Resource Identifier parsing with full Unicode support, Punycode encoding/decoding, and IDNA 2008 domain name processing.

## Parsing

Parse IRI strings containing Unicode characters:

@example('networking/iri-parsing.php')

Input is NFC-normalized and validated against RFC 3987 character ranges. Private-use characters are only permitted in the query component.

## Converting to URI

Convert an IRI to an ASCII-only RFC 3986 URI. International domain names are Punycode-encoded and Unicode path/query/fragment characters are percent-encoded:

@example('networking/iri-to-uri.php')

## Converting from URI

Reverse the process — decode a URI back to an IRI with Unicode characters restored:

@example('networking/iri-from-uri.php')

## Standards

| RFC | Title |
|-----|-------|
| [RFC 3987](https://datatracker.ietf.org/doc/html/rfc3987) | Internationalized Resource Identifiers (IRIs) |
| [RFC 3492](https://datatracker.ietf.org/doc/html/rfc3492) | Punycode: Bootstring Encoding for IDNA |
| [RFC 5891](https://datatracker.ietf.org/doc/html/rfc5891) | IDNA 2008: Protocol |
| [RFC 5892](https://datatracker.ietf.org/doc/html/rfc5892) | IDNA 2008: Unicode Code Points |

See `src/Psl/IRI/` for the full API.
