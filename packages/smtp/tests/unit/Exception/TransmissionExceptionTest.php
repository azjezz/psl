<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Exception\ExceptionInterface;
use Psl\SMTP\Exception\RuntimeException;
use Psl\SMTP\Exception\TransmissionException;

final class TransmissionExceptionTest extends TestCase
{
    public function testImplementsExceptionInterface(): void
    {
        $e = TransmissionException::forSenderRejected('user@example.com', 550, 'Rejected');

        static::assertInstanceOf(ExceptionInterface::class, $e);
        static::assertInstanceOf(RuntimeException::class, $e);
    }

    public function testForSenderRejected(): void
    {
        $e = TransmissionException::forSenderRejected('sender@example.com', 550, 'Sender not allowed');

        static::assertStringContainsString('sender@example.com', $e->getMessage());
        static::assertStringContainsString('550', $e->getMessage());
        static::assertStringContainsString('Sender not allowed', $e->getMessage());
        static::assertStringContainsString('rejected sender', $e->getMessage());
    }

    public function testForSenderRejectedBounce(): void
    {
        $e = TransmissionException::forSenderRejected('<>', 550, 'Null sender not allowed');

        static::assertStringContainsString('<>', $e->getMessage());
    }

    public function testForRecipientRejected(): void
    {
        $e = TransmissionException::forRecipientRejected('user@example.com', 550, 'User not found');

        static::assertStringContainsString('user@example.com', $e->getMessage());
        static::assertStringContainsString('550', $e->getMessage());
        static::assertStringContainsString('User not found', $e->getMessage());
        static::assertStringContainsString('rejected recipient', $e->getMessage());
    }

    public function testForRecipientRejectedWith452(): void
    {
        $e = TransmissionException::forRecipientRejected('user@example.com', 452, 'Insufficient storage');

        static::assertStringContainsString('452', $e->getMessage());
        static::assertStringContainsString('Insufficient storage', $e->getMessage());
    }

    public function testForDataRejected(): void
    {
        $e = TransmissionException::forDataRejected(554, 'Message too big');

        static::assertStringContainsString('554', $e->getMessage());
        static::assertStringContainsString('Message too big', $e->getMessage());
        static::assertStringContainsString('rejected message data', $e->getMessage());
    }

    public function testForDataRejectedWith451(): void
    {
        $e = TransmissionException::forDataRejected(451, 'Temporary failure');

        static::assertStringContainsString('451', $e->getMessage());
        static::assertStringContainsString('Temporary failure', $e->getMessage());
    }

    public function testForDataRejectedWith552(): void
    {
        $e = TransmissionException::forDataRejected(552, 'Message size exceeds limit');

        static::assertStringContainsString('552', $e->getMessage());
    }
}
