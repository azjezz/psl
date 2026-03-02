# CIDR

The `CIDR` component provides utilities for working with CIDR (Classless Inter-Domain Routing) notation. It supports both IPv4 and IPv6 addresses.

IPv4 addresses are internally normalized to IPv4-mapped IPv6 for unified comparison, so mixed-family matching works transparently.

## Usage

@example('networking/cidr-usage.php')

## Examples

### IPv4

@example('networking/cidr-ipv4.php')

### IPv6

@example('networking/cidr-ipv6.php')

### Single Host

@example('networking/cidr-single-host.php')

See `src/Psl/CIDR/` for the full API.
