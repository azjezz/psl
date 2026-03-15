<?php

declare(strict_types=1);

namespace Psl\TLS\Internal;

use Psl\TLS;

use function implode;
use function stream_context_create;

/**
 * Build an SSL context options array from {@see TLS\ServerConfig}.
 *
 * @return array<string, mixed>
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function server_ssl_context(TLS\ServerConfig $tls): array
{
    $ssl = [
        'local_cert' => $tls->certificate->certificateFile,
        'local_pk' => $tls->certificate->keyFile,
        'security_level' => $tls->securityLevel,
        'capture_peer_cert' => true,
        'capture_peer_cert_chain' => true,
        'session_tickets' => $tls->sessionTickets,
    ];

    if (null !== $tls->certificate->passphrase) {
        $ssl['passphrase'] = $tls->certificate->passphrase;
    }

    if (null !== $tls->certificateAuthority) {
        $ssl['cafile'] = $tls->certificateAuthority;
    }

    if (null !== $tls->certificateAuthorityPath) {
        $ssl['capath'] = $tls->certificateAuthorityPath;
    }

    if (null !== $tls->ciphers) {
        $ssl['ciphers'] = $tls->ciphers;
    }

    if (null !== $tls->alpnProtocols) {
        $ssl['alpn_protocols'] = implode(',', $tls->alpnProtocols);
    }

    if ([] !== $tls->sniCertificates) {
        $sniCerts = [];
        foreach ($tls->sniCertificates as $hostname => $certificate) {
            $sniSsl = [
                'local_cert' => $certificate->certificateFile,
                'local_pk' => $certificate->keyFile,
            ];

            if (null !== $certificate->passphrase) {
                $sniSsl['passphrase'] = $certificate->passphrase;
            }

            $sniCerts[$hostname] = stream_context_create(['ssl' => $sniSsl]);
        }

        $ssl['SNI_server_certs'] = $sniCerts;
    }

    return $ssl;
}
