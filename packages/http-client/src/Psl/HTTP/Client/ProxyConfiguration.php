<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Psl\URL\URL;

/**
 * Configuration for an HTTP proxy used by the HTTP client.
 *
 * ## Proxy behavior by target scheme
 *
 * The proxy operates differently depending on the target request's scheme:
 *
 * - **HTTP targets**: The client connects to the proxy directly and sends
 *   the request with an absolute-form request-target per RFC 7230 Section 5.3.2
 *   (forward proxying). The proxy relays the request to the target server.
 * - **HTTPS targets**: The client connects to the proxy and issues an HTTP/1.1
 *   CONNECT request to establish a tunnel to the target host:port. Once the
 *   tunnel is established, TLS and HTTP framing happen end-to-end through
 *   the transparent byte pipe.
 *
 * ## Proxy URL
 *
 * The {@see $url} field specifies the proxy address. The scheme determines
 * whether the connection to the proxy itself uses TLS:
 *
 * - `http://proxy:8080`: Plaintext connection to the proxy.
 * - `https://proxy:443`: TLS connection to the proxy (the proxy itself
 *   terminates TLS, distinct from the end-to-end TLS of CONNECT tunnels).
 *
 * ## Authentication
 *
 * The {@see $authorization} field holds the value of the `Proxy-Authorization`
 * header sent with every request through the proxy. Common schemes include
 * `Basic <base64>` and `Bearer <token>`. When null, no authorization header
 * is sent.
 *
 * ## TLS SNI for HTTPS proxies
 *
 * When the proxy URL uses HTTPS, TLS Server Name Indication (SNI) is required
 * for certificate validation. The {@see $sni} field specifies the hostname to
 * send during the TLS handshake to the proxy. This is useful when a
 * DNS-resolving connector replaces the proxy hostname with a resolved IP
 * address but the original hostname is still needed for TLS. When null, the
 * host from {@see $url} is used for SNI.
 *
 * ## Proxy bypass
 *
 * The {@see $skipProxyFor} list specifies hosts that bypass the proxy and
 * connect directly. Rules support:
 *
 * - Wildcard: `"*"` bypasses all hosts.
 * - Exact match: `"localhost"` matches only `"localhost"`.
 * - Domain suffix: `".example.com"` or `"example.com"` matches any subdomain
 *   (e.g., `"api.example.com"`, `"sub.deep.example.com"`).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7230#section-5.3.2 absolute-form
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.6 CONNECT method
 *
 * @api
 */
final readonly class ProxyConfiguration
{
    /**
     * The proxy address.
     *
     * The scheme determines whether the connection to the proxy uses TLS
     * (`https://`) or plaintext (`http://`). The host and port identify
     * the proxy server.
     */
    public URL $url;

    /**
     * The `Proxy-Authorization` header value, or null if no authentication
     * is required.
     *
     * Typically `"Basic <base64>"` for username/password authentication.
     *
     * @var null|non-empty-string
     */
    public null|string $authorization;

    /**
     * The hostname for TLS SNI when connecting to an HTTPS proxy, or null
     * to use the host from {@see $url}.
     *
     * Set by DNS-resolving connectors that replace the proxy hostname with
     * a resolved IP address but need to preserve the original hostname for
     * TLS certificate validation.
     *
     * @var null|non-empty-string
     */
    public null|string $sni;

    /**
     * Hosts that bypass the proxy and connect directly.
     *
     * Supports wildcard (`"*"`), exact match (`"localhost"`), and domain
     * suffix match (`".example.com"` or `"example.com"`).
     *
     * @var list<non-empty-string>
     */
    public array $skipProxyFor;

    /**
     * @param URL $url The proxy address (e.g., `http://proxy:8080` or `https://proxy:443`).
     * @param null|non-empty-string $authorization The `Proxy-Authorization` header value, or null for no auth.
     * @param null|non-empty-string $sni The TLS SNI hostname for HTTPS proxies, or null to use the URL host.
     * @param list<non-empty-string> $skipProxyFor Hosts that bypass the proxy.
     */
    public function __construct(
        URL $url,
        null|string $authorization = null,
        null|string $sni = null,
        array $skipProxyFor = [],
    ) {
        $this->url = $url;
        $this->authorization = $authorization;
        $this->sni = $sni;
        $this->skipProxyFor = $skipProxyFor;
    }

    /**
     * Return a new configuration with the given proxy URL.
     *
     * @param URL $url The proxy address.
     */
    public function withUrl(URL $url): self
    {
        return new self($url, $this->authorization, $this->sni, $this->skipProxyFor);
    }

    /**
     * Return a new configuration with the given authorization header value.
     *
     * @param null|non-empty-string $authorization The `Proxy-Authorization` header value (e.g., `"Basic dXNlcjpwYXNz"`), or null to disable proxy authentication.
     */
    public function withAuthorization(null|string $authorization): self
    {
        return new self($this->url, $authorization, $this->sni, $this->skipProxyFor);
    }

    /**
     * Return a new configuration with the given TLS SNI hostname.
     *
     * @param null|non-empty-string $sni The hostname for TLS SNI when connecting to the proxy, or null to use the host from the URL.
     */
    public function withSni(null|string $sni): self
    {
        return new self($this->url, $this->authorization, $sni, $this->skipProxyFor);
    }

    /**
     * Return a new configuration with the given proxy bypass list.
     *
     * @param list<non-empty-string> $skipProxyFor Hosts that bypass the proxy. Supports wildcard ("*"), exact match ("localhost"), and domain suffix (".example.com").
     */
    public function withSkipProxyFor(array $skipProxyFor): self
    {
        return new self($this->url, $this->authorization, $this->sni, $skipProxyFor);
    }
}
