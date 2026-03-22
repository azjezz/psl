<?php

declare(strict_types=1);

namespace Psl\SMTP\Client\Authentication;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\SMTP\Client\ConnectionInterface;
use Psl\SMTP\Exception\AuthenticationException;
use SensitiveParameter;

use function base64_decode;
use function base64_encode;
use function hash_hmac;
use function str_contains;
use function strtoupper;

/**
 * AUTH CRAM-MD5 mechanism per RFC 2195.
 *
 * Challenge-response authentication that avoids sending the password in
 * plaintext. The server sends a challenge, and the client responds with
 * an HMAC-MD5 digest of the challenge keyed by the password.
 *
 * 1. Client: AUTH CRAM-MD5
 * 2. Server: 334 <base64-encoded challenge>
 * 3. Client: base64(username " " HMAC-MD5(password, challenge))
 * 4. Server: 235 on success
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2195
 *
 * @api
 */
final readonly class CRAMMD5Authenticator implements AuthenticatorInterface
{
    public string $mechanism;

    public function __construct(
        private string $username,
        #[SensitiveParameter]
        private string $password,
    ) {
        $this->mechanism = 'CRAM-MD5';
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

        $response = $connection->sendCommand('AUTH CRAM-MD5', $cancellation);
        if ($response->code !== 334) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }

        $challenge = base64_decode($response->message, true);
        if ($challenge === false) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }

        $digest = hash_hmac('md5', $challenge, $this->password);
        $payload = base64_encode($this->username . ' ' . $digest);

        $response = $connection->sendCommand($payload, $cancellation);
        if (!$response->isPositiveCompletion()) {
            throw AuthenticationException::forRejected($this->mechanism, $response->code, $response->message);
        }
    }
}
