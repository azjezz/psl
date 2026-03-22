<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\DKIM;

use PHPUnit\Framework\TestCase;
use Psl\MIME\DKIM\Algorithm;
use Psl\MIME\DKIM\Canonicalization;
use Psl\MIME\DKIM\Signer;
use Psl\MIME\DKIM\SignerInterface;
use Psl\MIME\DKIM\SigningConfiguration;
use Psl\MIME\Exception\DKIMException;
use Psl\MIME\Tests\Fixture\Certificates;

use function preg_match;
use function str_repeat;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;
use function trim;

final class SignerTest extends TestCase
{
    private function createSigner(null|SigningConfiguration $config = null): Signer
    {
        return new Signer(Certificates::DKIM_RSA_KEY, $config ?? new SigningConfiguration('example.com', 'default'));
    }

    public function testImplementsInterface(): void
    {
        $signer = $this->createSigner();

        static::assertInstanceOf(SignerInterface::class, $signer);
    }

    public function testSignAddsHeader(): void
    {
        $message = "From: sender@example.com\r\nTo: rcpt@example.com\r\nSubject: Test\r\n\r\nHello, World!";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        static::assertTrue(str_starts_with($signed, 'DKIM-Signature:'));
    }

    public function testSignContainsRequiredTags(): void
    {
        $message = "From: sender@example.com\r\nTo: rcpt@example.com\r\nSubject: Test\r\n\r\nBody";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);
        $headerEnd = strpos($signed, "\r\nFrom:");
        $dkimHeader = substr($signed, 0, $headerEnd);

        static::assertStringContainsString('v=1', $dkimHeader);
        static::assertStringContainsString('a=rsa-sha256', $dkimHeader);
        static::assertStringContainsString('d=example.com', $dkimHeader);
        static::assertStringContainsString('s=default', $dkimHeader);
        static::assertStringContainsString('bh=', $dkimHeader);
        static::assertStringContainsString('h=', $dkimHeader);
        static::assertStringContainsString('b=', $dkimHeader);
        static::assertStringContainsString('t=', $dkimHeader);
        static::assertStringContainsString('q=dns/txt', $dkimHeader);
    }

    public function testSignIncludesFromHeader(): void
    {
        $message = "From: sender@example.com\r\nTo: rcpt@example.com\r\n\r\nBody";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        static::assertStringContainsString('h=from', $signed);
    }

    public function testSignIgnoresReturnPath(): void
    {
        $message = "Return-Path: <bounce@example.com>\r\nFrom: sender@example.com\r\n\r\nBody";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        $headerEnd = strpos($signed, "\r\nReturn-Path:");
        if (false === $headerEnd) {
            $headerEnd = strpos($signed, "\r\nFrom:");
        }

        $dkimHeader = substr($signed, 0, $headerEnd);
        static::assertStringNotContainsString('return-path', $dkimHeader);
    }

    public function testSignIgnoresCustomHeaders(): void
    {
        $config = new SigningConfiguration('example.com', 'default', headersToIgnore: ['x-custom']);
        $message = "From: sender@example.com\r\nX-Custom: value\r\n\r\nBody";
        $signer = $this->createSigner($config);

        $signed = $signer->sign($message);

        $headerEnd = strpos($signed, "\r\nFrom:");
        $dkimHeader = substr($signed, 0, $headerEnd);
        static::assertStringNotContainsString('x-custom', $dkimHeader);
    }

    public function testSignPreservesOriginalMessage(): void
    {
        $message = "From: sender@example.com\r\nTo: rcpt@example.com\r\n\r\nHello!";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        static::assertStringContainsString("From: sender@example.com\r\n", $signed);
        static::assertStringContainsString("To: rcpt@example.com\r\n", $signed);
        static::assertStringEndsWith("\r\n\r\nHello!", $signed);
    }

    public function testSignWithRelaxedCanonicalization(): void
    {
        $config = new SigningConfiguration(
            'example.com',
            'default',
            headerCanonicalization: Canonicalization::Relaxed,
            bodyCanonicalization: Canonicalization::Relaxed,
        );
        $message = "From: sender@example.com\r\nTo: rcpt@example.com\r\n\r\nBody";
        $signer = $this->createSigner($config);

        $signed = $signer->sign($message);

        static::assertStringContainsString('c=relaxed/relaxed', $signed);
    }

    public function testSignWithSimpleCanonicalization(): void
    {
        $config = new SigningConfiguration(
            'example.com',
            'default',
            headerCanonicalization: Canonicalization::Simple,
            bodyCanonicalization: Canonicalization::Simple,
        );
        $message = "From: sender@example.com\r\n\r\nBody";
        $signer = $this->createSigner($config);

        $signed = $signer->sign($message);

        static::assertStringContainsString('c=simple/simple', $signed);
    }

    public function testSignWithExpiration(): void
    {
        $config = new SigningConfiguration('example.com', 'default', signatureExpirationDelay: 3600);
        $message = "From: sender@example.com\r\n\r\nBody";
        $signer = $this->createSigner($config);

        $signed = $signer->sign($message);

        static::assertStringContainsString('x=', $signed);
    }

    public function testSignWithoutExpiration(): void
    {
        $message = "From: sender@example.com\r\n\r\nBody";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        $headerEnd = strpos($signed, "\r\nFrom:");
        $dkimHeader = substr($signed, 0, $headerEnd);
        static::assertStringNotContainsString('x=', $dkimHeader);
    }

    public function testSignWithBodyLength(): void
    {
        $config = new SigningConfiguration('example.com', 'default', includeBodyLength: true);
        $message = "From: sender@example.com\r\n\r\nBody content here";
        $signer = $this->createSigner($config);

        $signed = $signer->sign($message);

        static::assertStringContainsString('l=', $signed);
    }

    public function testSignWithBodyMaxLength(): void
    {
        $config = new SigningConfiguration('example.com', 'default', bodyMaxLength: 10, includeBodyLength: true);
        $message = "From: sender@example.com\r\n\r\n" . str_repeat('A', 1000);
        $signer = $this->createSigner($config);

        $signed = $signer->sign($message);

        static::assertStringContainsString('l=10', $signed);
    }

    public function testSignDeterministicBodyHash(): void
    {
        $message = "From: sender@example.com\r\n\r\nSame body";
        $signer = $this->createSigner();

        $signed1 = $signer->sign($message);
        $signed2 = $signer->sign($message);

        $bh1 = self::extractTag($signed1, 'bh');
        $bh2 = self::extractTag($signed2, 'bh');

        static::assertSame($bh1, $bh2);
    }

    public function testSignEmptyBody(): void
    {
        $message = "From: sender@example.com\r\n\r\n";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        static::assertTrue(str_starts_with($signed, 'DKIM-Signature:'));
        static::assertStringContainsString('bh=', $signed);
    }

    public function testSignWithInvalidKeyThrows(): void
    {
        $signer = new Signer('not-a-valid-key', new SigningConfiguration('example.com', 'default'));

        $this->expectException(DKIMException::class);

        $signer->sign("From: a@b.com\r\n\r\nBody");
    }

    public function testSigningConfigurationWithMethods(): void
    {
        $config = new SigningConfiguration('example.com', 'sel1');

        $updated = $config
            ->withAlgorithm(Algorithm::Ed25519Sha256)
            ->withHeaderCanonicalization(Canonicalization::Simple)
            ->withBodyCanonicalization(Canonicalization::Simple)
            ->withSignatureExpirationDelay(7200)
            ->withBodyMaxLength(1024)
            ->withIncludeBodyLength()
            ->withHeadersToIgnore(['x-mailer']);

        static::assertSame(Algorithm::Ed25519Sha256, $updated->algorithm);
        static::assertSame(Canonicalization::Simple, $updated->headerCanonicalization);
        static::assertSame(Canonicalization::Simple, $updated->bodyCanonicalization);
        static::assertSame(7200, $updated->signatureExpirationDelay);
        static::assertSame(1024, $updated->bodyMaxLength);
        static::assertTrue($updated->includeBodyLength);
        static::assertSame(['x-mailer'], $updated->headersToIgnore);

        static::assertSame(Algorithm::RsaSha256, $config->algorithm);
        static::assertSame(Canonicalization::Relaxed, $config->headerCanonicalization);
    }

    public function testSignSkipsExistingDkimSignature(): void
    {
        $message = "DKIM-Signature: v=1; old\r\nFrom: sender@example.com\r\n\r\nBody";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        static::assertTrue(str_starts_with($signed, 'DKIM-Signature:'));
        $secondDkim = strpos($signed, 'DKIM-Signature:', strlen('DKIM-Signature:'));
        static::assertNotFalse($secondDkim);
    }

    public function testSignMultipleHeaders(): void
    {
        $message = "From: sender@example.com\r\nTo: rcpt@example.com\r\nSubject: Test\r\nDate: Thu, 1 Jan 2026 00:00:00 +0000\r\nMessage-ID: <123@example.com>\r\n\r\nBody";
        $signer = $this->createSigner();

        $signed = $signer->sign($message);

        $headerEnd = strpos($signed, "\r\nFrom:");
        $dkimHeader = substr($signed, 0, $headerEnd);
        static::assertStringContainsString('from', $dkimHeader);
        static::assertStringContainsString('to', $dkimHeader);
        static::assertStringContainsString('subject', $dkimHeader);
        static::assertStringContainsString('date', $dkimHeader);
        static::assertStringContainsString('message-id', $dkimHeader);
    }

    private static function extractTag(string $header, string $tag): string
    {
        $pattern = '/' . $tag . '=([^;]+)/';
        if (preg_match($pattern, $header, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }
}
