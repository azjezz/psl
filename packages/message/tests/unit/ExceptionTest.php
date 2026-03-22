<?php

declare(strict_types=1);

namespace Psl\Message\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Message\Exception\InvalidArgumentException;
use Psl\Message\Exception\InvalidMailboxException;
use Psl\Message\Exception\ParsingException;
use Psl\Message\Exception\RuntimeException;

final class ExceptionTest extends TestCase
{
    #[Test]
    public function invalidMailboxForEmptyLocalPart(): void
    {
        $e = InvalidMailboxException::forEmptyLocalPart();

        self::assertInstanceOf(InvalidMailboxException::class, $e);
        self::assertInstanceOf(InvalidArgumentException::class, $e);
        self::assertStringContainsString('local-part', $e->getMessage());
    }

    #[Test]
    public function invalidMailboxForEmptyDomain(): void
    {
        $e = InvalidMailboxException::forEmptyDomain();

        self::assertInstanceOf(InvalidMailboxException::class, $e);
        self::assertStringContainsString('domain', $e->getMessage());
    }

    #[Test]
    public function invalidMailboxForInvalidLocalPart(): void
    {
        $e = InvalidMailboxException::forInvalidLocalPart('bad local');

        self::assertInstanceOf(InvalidMailboxException::class, $e);
        self::assertStringContainsString('bad local', $e->getMessage());
    }

    #[Test]
    public function invalidMailboxForInvalidDomain(): void
    {
        $e = InvalidMailboxException::forInvalidDomain('bad domain');

        self::assertInstanceOf(InvalidMailboxException::class, $e);
        self::assertStringContainsString('bad domain', $e->getMessage());
    }

    #[Test]
    public function parsingExceptionForInvalidMailbox(): void
    {
        $e = ParsingException::forInvalidMailbox('bad@');

        self::assertInstanceOf(ParsingException::class, $e);
        self::assertInstanceOf(RuntimeException::class, $e);
        self::assertStringContainsString('bad@', $e->getMessage());
    }

    #[Test]
    public function parsingExceptionForInvalidGroup(): void
    {
        $e = ParsingException::forInvalidGroup('bad group');

        self::assertInstanceOf(ParsingException::class, $e);
        self::assertStringContainsString('bad group', $e->getMessage());
    }

    #[Test]
    public function parsingExceptionForInvalidMessageId(): void
    {
        $e = ParsingException::forInvalidMessageId('<>');

        self::assertInstanceOf(ParsingException::class, $e);
        self::assertStringContainsString('<>', $e->getMessage());
    }

    #[Test]
    public function parsingExceptionForInvalidAddressList(): void
    {
        $e = ParsingException::forInvalidAddressList('bad list');

        self::assertInstanceOf(ParsingException::class, $e);
        self::assertStringContainsString('bad list', $e->getMessage());
    }

    #[Test]
    public function parsingExceptionForMalformedMessage(): void
    {
        $e = ParsingException::forMalformedMessage('no separator');

        self::assertInstanceOf(ParsingException::class, $e);
        self::assertStringContainsString('no separator', $e->getMessage());
    }

    #[Test]
    public function invalidArgumentExceptionForEmptyRecipients(): void
    {
        $e = InvalidArgumentException::forEmptyRecipients();

        self::assertInstanceOf(InvalidArgumentException::class, $e);
        self::assertStringContainsString('recipient', $e->getMessage());
    }

    #[Test]
    public function runtimeExceptionForNoRecipients(): void
    {
        $e = RuntimeException::forNoRecipients();

        self::assertInstanceOf(RuntimeException::class, $e);
        self::assertStringContainsString('recipient', $e->getMessage());
    }
}
