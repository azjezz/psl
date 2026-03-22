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
 * XOAUTH2 authentication for OAuth2-enabled SMTP servers.
 *
 * Sends: AUTH XOAUTH2 base64("user=" . user . "\x01auth=Bearer " . token . "\x01\x01")
 *
 * @link https://developers.google.com/gmail/imap/xoauth2-protocol
 *
 * @api
 */
final readonly class XOAuth2Authenticator implements AuthenticatorInterface
{
    public string $mechanism;

    public function __construct(
        private string $username,
        #[SensitiveParameter]
        private string $accessToken,
    ) {
        $this->mechanism = 'XOAUTH2';
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

        $payload = base64_encode('user=' . $this->username . "\x01auth=Bearer " . $this->accessToken . "\x01\x01");
        $response = $connection->sendCommand('AUTH XOAUTH2 ' . $payload, $cancellation);

        if (!$response->isPositiveCompletion()) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }
    }
}
