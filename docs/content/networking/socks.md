# Socks

The `Socks` component provides a SOCKS5 proxy connector that tunnels TCP connections through a SOCKS5 proxy server (RFC 1928).

It implements `TCP\ConnectorInterface`, so it can be used anywhere a TCP connector is expected -- enabling transparent proxy tunneling for any code that accepts a connector.

## Usage

@example('networking/socks-connect.php')

With username/password authentication (RFC 1929):

@example('networking/socks-auth.php')

## Examples

### Tunneling TLS Through a Proxy

@example('networking/socks-tls-tunnel.php')

### With Connection Pooling

@example('networking/socks-pool.php')

### With Retry

@example('networking/socks-retry.php')

See `src/Psl/Socks/` for the full API.
