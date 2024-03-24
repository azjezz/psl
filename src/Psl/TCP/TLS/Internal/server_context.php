<?php

declare(strict_types=1);

namespace Psl\TCP\TLS\Internal;

use Psl\Dict;
use Psl\Str;
use Psl\TCP\TLS;

function server_context(TLS\ServerOptions $options): array
{
    $ssl_context = [
        'crypto_method' => match ($options->minimumVersion) {
            TLS\Version::Tls10 => STREAM_CRYPTO_METHOD_TLSv1_0_SERVER | STREAM_CRYPTO_METHOD_TLSv1_1_SERVER | STREAM_CRYPTO_METHOD_TLSv1_2_SERVER | STREAM_CRYPTO_METHOD_TLSv1_3_SERVER,
            TLS\Version::Tls11 => STREAM_CRYPTO_METHOD_TLSv1_1_SERVER | STREAM_CRYPTO_METHOD_TLSv1_2_SERVER | STREAM_CRYPTO_METHOD_TLSv1_3_SERVER,
            TLS\Version::Tls12 => STREAM_CRYPTO_METHOD_TLSv1_2_SERVER | STREAM_CRYPTO_METHOD_TLSv1_3_SERVER,
            TLS\Version::Tls13 => STREAM_CRYPTO_METHOD_TLSv1_3_SERVER,
        },
        'peer_name' => $options->peerName,
        'verify_peer' => $options->peerVerification,
        'allow_self_signed' => $options->allowSelfSignedCertificates,
        'verify_peer_name' => $options->peerVerification,
        'verify_depth' => $options->verificationDepth,
        'ciphers' => $options->ciphers ?? OPENSSL_DEFAULT_STREAM_CIPHERS,
        'capture_peer_cert' => $options->capturePeerCertificate,
        'capture_peer_cert_chain' => $options->capturePeerCertificate,
        'security_level' => $options->securityLevel->value,
        'honor_cipher_order' => true,
        'single_dh_use' => true,
        'no_ticket' => true,
    ];

    if ([] !== $options->alpnProtocols) {
        $ssl_context['alpn_protocols'] = Str\join($options->alpnProtocols, ',');
    }

    if (null !== $options->defaultCertificate) {
        $ssl_context['local_cert'] = $options->defaultCertificate->certificateFile;
        if ($options->defaultCertificate->certificateFile !== $options->defaultCertificate->keyFile) {
            $ssl_context['local_pk'] = $options->defaultCertificate->keyFile;
        }

        if (null !== $options->defaultCertificate->passphrase) {
            $ssl_context['passphrase'] = $options->defaultCertificate->passphrase;
        }
    }

    if ([] !== $options->certificates) {
        $ssl_context['SNI_server_certs'] = Dict\map(
            $options->certificates,
            /**
             * @returns array{local_cert: non-empty-string, local_pk: non-empty-string, passphrase?: non-empty-string}
             */
            static function (TLS\Certificate $certificate): array {
                $options = [
                    'local_cert' => $certificate->certificateFile,
                    'local_pk' => $certificate->keyFile,
                ];

                if (null !== $certificate->passphrase) {
                    $options['passphrase'] = $certificate->passphrase;
                }

                return $options;
            },
        );
    }

    if (null !== $options->certificateAuthorityFile) {
        $ssl_context['cafile'] = $options->certificateAuthorityFile;
    }

    if (null !== $options->certificateAuthorityPath) {
        $ssl_context['capath'] = $options->certificateAuthorityPath;
    }

    return $ssl_context;
}
