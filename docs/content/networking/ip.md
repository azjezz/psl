# IP

The `IP` component provides an immutable, binary-backed value object for working with IPv4 and IPv6 addresses. It supports parsing, formatting, classification, comparison, and reverse DNS lookups.

`Address` implements `Stringable`, `Comparable`, and `Equable`.

## Usage

@example('networking/ip-usage.php')

## Parsing

There are three ways to create an `Address`:

- **`Address::v4()`** -- parse a dotted-decimal IPv4 address.
- **`Address::v6()`** -- parse a colon-hex IPv6 address.
- **`Address::parse()`** -- auto-detect the family.

You can also create an address from raw binary bytes using `Address::fromBytes()`.

@example('networking/ip-parse.php')

## Classification

`Address` provides methods to classify addresses into well-known categories:

| Method | IPv4 Range | IPv6 Range |
|---|---|---|
| `isLoopback()` | `127.0.0.0/8` | `::1` |
| `isPrivate()` | `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16` | `fc00::/7` |
| `isLinkLocal()` | `169.254.0.0/16` | `fe80::/10` |
| `isMulticast()` | `224.0.0.0/4` | `ff00::/8` |
| `isUnspecified()` | `0.0.0.0` | `::` |
| `isDocumentation()` | `192.0.2.0/24`, `198.51.100.0/24`, `203.0.113.0/24` | `2001:db8::/32` |
| `isGlobalUnicast()` | Everything else | Everything else |

@example('networking/ip-classify.php')

## Formatting

@example('networking/ip-format.php')

## Reverse DNS

@example('networking/ip-arpa.php')

## Comparison

`Address` implements `Comparable` and `Equable`, so addresses can be compared and sorted.

@example('networking/ip-compare.php')

## CIDR Integration

`Address` objects can be passed directly to `CIDR\Block::contains()`:

@example('networking/ip-cidr.php')

## Family

The `Family` enum represents the IP address family, with values `V4` (4) and `V6` (16) corresponding to their byte sizes. It also supports conversion to and from IANA address family numbers (RFC 7871).

@example('networking/ip-family.php')

See `src/Psl/IP/` for the full API.
