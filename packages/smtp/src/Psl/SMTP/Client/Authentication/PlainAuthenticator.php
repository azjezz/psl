<?php

declare(strict_types=1);

namespace Psl\SMTP\Client\Authentication;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\SMTP\Client\ConnectionInterface;
use Psl\SMTP\Exception\AuthenticationException;
use SensitiveParameter;

use function base64_encode;
use function str_contains;
use function strtoupper;

/**
 * AUTH PLAIN mechanism per RFC 4616.
 *
 * Sends: AUTH PLAIN base64(\0username\0password)
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4616
 *
 * @api
 */
final readonly class PlainAuthenticator implements AuthenticatorInterface
{
    public string $mechanism;

    public function __construct(
        private string $username,
        #[SensitiveParameter]
        private string $password,
        private string $authorizationIdentity = '',
    ) {
        $this->mechanism = 'PLAIN';
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(
        ConnectionInterface $connection,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $authValue = $connection->getCapabilityValue('AUTH');
        if ($authValue === null || !str_contains(' ' . strtoupper($authValue) . ' ', ' ' . $this->mechanism . ' ')) {
            throw AuthenticationException::forUnsupportedMechanism($this->mechanism);
        }

        $payload = base64_encode($this->authorizationIdentity . "\0" . $this->username . "\0" . $this->password);
        $response = $connection->sendCommand('AUTH PLAIN ' . $payload, $cancellation);

        if (!$response->isPositiveCompletion()) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }
    }
}
