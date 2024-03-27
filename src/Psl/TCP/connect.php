<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\IO\Internal\OptionalIncrementalTimeout;
use Psl\Network;
use Psl\Network\Exception;
use Psl\Str;
use Psl\TCP\TLS\Exception\NegotiationException;
use Revolt\EventLoop;

use function feof;
use function restore_error_handler;
use function set_error_handler;
use function stream_context_set_option;
use function stream_context_set_options;
use function stream_socket_enable_crypto;

use const OPENSSL_DEFAULT_STREAM_CIPHERS;
use const PHP_VERSION_ID;
use const STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;

/**
 * Connect to a socket.
 *
 * @param non-empty-string $host
 * @param int<0, max> $port
 *
 * @throws Network\Exception\RuntimeException If failed to connect to client on the given address.
 * @throws Network\Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 */
function connect(
    string $host,
    int $port = 0,
    ?ConnectOptions $options = null,
    ?float $timeout = null,
): Network\StreamSocketInterface {
    $optional_timeout = new OptionalIncrementalTimeout($timeout, static function (): void {
        throw new Exception\TimeoutException('Connection to socket timed out.');
    });

    $options ??= ConnectOptions::create();

    $context = [
        'socket' => [
            'tcp_nodelay' => $options->noDelay,
        ],
    ];

    if (null !== $options->bindTo) {
        $context['socket']['bindto'] = $options->bindTo[0] . ':' . ((string) ($options->bindTo[1] ?? 0));
    }

    $socket = Network\Internal\socket_connect("tcp://$host:$port", $context, $optional_timeout->getRemaining());
    $tls_options = $options->TLSConnectOptions;
    if (null !== $tls_options) {
        if ($tls_options->peerName === '') {
            $tls_options = $tls_options->withPeerName($host);
        }

        $context = [
            'crypto_method' => match ($tls_options->minimumVersion) {
                TLS\Version::Tls10 => STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
                TLS\Version::Tls11 => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
                TLS\Version::Tls12 => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
                TLS\Version::Tls13 => STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
            },
            'peer_name' => $tls_options->peerName,
            'verify_peer' => $tls_options->peerVerification,
            'verify_peer_name' => $tls_options->peerVerification,
            'verify_depth' => $tls_options->verificationDepth,
            'ciphers' => $tls_options->ciphers ?? OPENSSL_DEFAULT_STREAM_CIPHERS,
            'capture_peer_cert' => $tls_options->capturePeerCertificate,
            'capture_peer_cert_chain' => $tls_options->capturePeerCertificate,
            'SNI_enabled' => $tls_options->SNIEnabled,
            'security_level' => $tls_options->securityLevel->value,
        ];

        if (null !== $tls_options->certificate) {
            $context['local_cert'] = $tls_options->certificate->certificateFile;
            if ($tls_options->certificate->certificateFile !== $tls_options->certificate->keyFile) {
                $context['local_pk'] = $tls_options->certificate->keyFile;
            }

            if ($tls_options->certificate->passphrase !== null) {
                $context['passphrase'] = $tls_options->certificate->passphrase;
            }
        }

        if ($tls_options->certificateAuthorityFile !== null) {
            $context['cafile'] = $tls_options->certificateAuthorityFile;
        }

        if ($tls_options->certificateAuthorityPath !== null) {
            $context['capath'] = $tls_options->certificateAuthorityPath;
        }

        if ([] !== $tls_options->ALPNProtocols) {
            $context['alpn_protocols'] = Str\join($tls_options->ALPNProtocols, ',');
        }

        if ($tls_options->peerFingerprints !== null) {
            $peer_fingerprints = [];
            foreach ($tls_options->peerFingerprints as $peer_fingerprint) {
                $peer_fingerprints[$peer_fingerprint[0]->value] = $peer_fingerprint[1];
            }

            $context['peer_fingerprint'] = $peer_fingerprints;
        }

        if (PHP_VERSION_ID >= 80300) {
            /** @psalm-suppress UnusedFunctionCall */
            stream_context_set_options($socket, ['ssl' => $context]);
        } else {
            stream_context_set_option($socket, ['ssl' => $context]);
        }


        $error_handler = static function (int $code, string $message) use ($socket): never {
            if (feof($socket)) {
                $message = 'Connection reset by peer';
            }

            throw new NegotiationException('TLS negotiation failed: ' . $message);
        };

        try {
            set_error_handler($error_handler);
            $result = stream_socket_enable_crypto($socket, enable: true);

            if ($result === false) {
                throw new NegotiationException('TLS negotiation failed: Unknown error');
            }
        } finally {
            restore_error_handler();
        }

        if (true !== $result) {
            while (true) {
                $suspension = EventLoop::getSuspension();

                $read_watcher = '';
                $timeout_watcher = '';
                $timeout = $optional_timeout->getRemaining();
                if (null !== $timeout) {
                    $timeout_watcher = EventLoop::delay($timeout, static function () use ($suspension, &$read_watcher, $socket) {
                        EventLoop::cancel($read_watcher);

                        /** @psalm-suppress RedundantCondition - it can be resource|closed-resource */
                        if (is_resource($socket)) {
                            fclose($socket);
                        }

                        $suspension->throw(new Exception\TimeoutException('Connection to socket timed out.'));
                    });
                }

                $read_watcher = EventLoop::onReadable($socket, static function () use ($suspension, $timeout_watcher) {
                    EventLoop::cancel($timeout_watcher);

                    $suspension->resume();
                });

                try {
                    $suspension->suspend();
                } finally {
                    EventLoop::cancel($read_watcher);
                    EventLoop::cancel($timeout_watcher);
                }

                try {
                    set_error_handler($error_handler);
                    $result = stream_socket_enable_crypto($socket, enable: true);
                    if ($result === false) {
                        $message = feof($socket) ? 'Connection reset by peer' : 'Unknown error';
                        throw new NegotiationException('TLS negotiation failed: ' . $message);
                    }
                } finally {
                    restore_error_handler();
                }

                if ($result === true) {
                    break;
                }
            }
        }
    }

    /** @psalm-suppress MissingThrowsDocblock */
    return new Network\Internal\Socket($socket);
}
