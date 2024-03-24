<?php

declare(strict_types=1);

namespace Psl\TCP\TLS\Internal;

use Psl\Str;
use Psl\TCP\TLS;

function client_context(string $host, TLS\ClientOptions $options): array
{
    if ($options->peerName === '') {
        $options = $options->withPeerName($host);
    }

    $context = [
        'crypto_method' => match ($options->minimumVersion) {
            TLS\Version::Tls10 => STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
            TLS\Version::Tls11 => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
            TLS\Version::Tls12 => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
            TLS\Version::Tls13 => STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
        },
        'peer_name' => $options->peerName,
        'verify_peer' => $options->peerVerification,
        'verify_peer_name' => $options->peerVerification,
        'verify_depth' => $options->verificationDepth,
        'ciphers' => $options->ciphers ?? OPENSSL_DEFAULT_STREAM_CIPHERS,
        'capture_peer_cert' => $options->capturePeerCertificate,
        'capture_peer_cert_chain' => $options->capturePeerCertificate,
        'SNI_enabled' => $options->SNIEnabled,
        'security_level' => $options->securityLevel->value,
    ];

    if (null !== $options->certificate) {
        $context['local_cert'] = $options->certificate->certificateFile;
        if ($options->certificate->certificateFile !== $options->certificate->keyFile) {
            $context['local_pk'] = $options->certificate->keyFile;
        }

        if ($options->certificate->passphrase !== null) {
            $context['passphrase'] = $options->certificate->passphrase;
        }
    }

    if ($options->certificateAuthorityFile !== null) {
        $context['cafile'] = $options->certificateAuthorityFile;
    }

    if ($options->certificateAuthorityPath !== null) {
        $context['capath'] = $options->certificateAuthorityPath;
    }

    if ([] !== $options->alpnProtocols) {
        $context['alpn_protocols'] = Str\join($options->alpnProtocols, ',');
    }

    if ($options->peerFingerprints !== null) {
        $peer_fingerprints = [];
        foreach ($options->peerFingerprints as $peer_fingerprint) {
            $peer_fingerprints[$peer_fingerprint[0]->value] = $peer_fingerprint[1];
        }

        $context['peer_fingerprint'] = $peer_fingerprints;
    }

    return $context;
}
