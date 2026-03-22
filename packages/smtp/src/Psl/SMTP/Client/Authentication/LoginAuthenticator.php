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
 * AUTH LOGIN mechanism.
 *
 * Three-step challenge-response exchange:
 * 1. Client sends AUTH LOGIN, server replies 334 (username prompt).
 * 2. Client sends base64(username), server replies 334 (password prompt).
 * 3. Client sends base64(password), server replies 235 on success.
 *
 * @api
 */
final readonly class LoginAuthenticator implements AuthenticatorInterface
{
    public string $mechanism;

    public function __construct(
        private string $username,
        #[SensitiveParameter]
        private string $password,
    ) {
        $this->mechanism = 'LOGIN';
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

        $response = $connection->sendCommand('AUTH LOGIN', $cancellation);
        if ($response->code !== 334) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }

        $response = $connection->sendCommand(base64_encode($this->username), $cancellation);
        if ($response->code !== 334) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }

        $response = $connection->sendCommand(base64_encode($this->password), $cancellation);
        if (!$response->isPositiveCompletion()) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }
    }
}
