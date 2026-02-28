<?php

declare(strict_types=1);

namespace Psl\TLS\Internal;

use Psl\TLS;

use function implode;

/**
 * Build an SSL context options array from {@see TLS\ClientConfig}.
 *
 * @return array<string, mixed>
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function client_ssl_context(TLS\ClientConfig $tls): array
{
    $ssl = [
        'verify_peer' => $tls->peerVerification,
        'verify_peer_name' => $tls->peerNameVerification ?? $tls->peerVerification,
        'allow_self_signed' => $tls->allowSelfSigned,
        'security_level' => $tls->securityLevel,
        'capture_peer_cert' => true,
        'capture_peer_cert_chain' => true,
        'session_tickets' => $tls->sessionTickets,
        'SNI_enabled' => $tls->sniEnabled,
        'verify_depth' => $tls->verificationDepth,
    ];

    if (null !== $tls->peerName) {
        $ssl['peer_name'] = $tls->peerName;
    }

    if (null !== $tls->certificateAuthority) {
        $ssl['cafile'] = $tls->certificateAuthority;
    }

    if (null !== $tls->certificateAuthorityPath) {
        $ssl['capath'] = $tls->certificateAuthorityPath;
    }

    if (null !== $tls->certificate) {
        $ssl['local_cert'] = $tls->certificate->certificateFile;
        $ssl['local_pk'] = $tls->certificate->keyFile;
        if (null !== $tls->certificate->passphrase) {
            $ssl['passphrase'] = $tls->certificate->passphrase;
        }
    }

    if (null !== $tls->minimumVersion || null !== $tls->maximumVersion) {
        $ssl['crypto_method'] = crypto_method($tls->minimumVersion, $tls->maximumVersion, server: false);
    }

    if (null !== $tls->ciphers) {
        $ssl['ciphers'] = $tls->ciphers;
    }

    if (null !== $tls->alpnProtocols) {
        $ssl['alpn_protocols'] = implode(',', $tls->alpnProtocols);
    }

    if (null !== $tls->peerFingerprints) {
        $ssl['peer_fingerprint'] = ['sha256' => $tls->peerFingerprints];
    }

    return $ssl;
}
