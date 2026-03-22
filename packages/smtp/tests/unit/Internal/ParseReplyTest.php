<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\SMTP\Exception\ProtocolException;

use function Psl\SMTP\Internal\parse_reply;
use function str_repeat;

final class ParseReplyTest extends TestCase
{
    public function testSingleLineResponse(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 OK\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertSame('OK', $response->message);
        static::assertTrue($response->isPositiveCompletion());
    }

    public function testMultiLineResponse(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250-mail.example.com\r\n250-SIZE 10485760\r\n250 PIPELINING\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertStringContainsString('mail.example.com', $response->message);
        static::assertStringContainsString('SIZE 10485760', $response->message);
        static::assertStringContainsString('PIPELINING', $response->message);
    }

    public function testNegativeResponse(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 User not found\r\n"));

        $response = parse_reply($reader);

        static::assertSame(550, $response->code);
        static::assertSame('User not found', $response->message);
        static::assertFalse($response->isPositiveCompletion());
    }

    public function testCodeOnlyNoMessage(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertSame('', $response->message);
    }

    public function testCodeWithSpaceAndEmptyMessage(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 \r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertSame('', $response->message);
    }

    public function testMalformedResponseTooShort(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("AB\r\n"));

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testMalformedResponseNonNumericCode(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("XYZ Invalid\r\n"));

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testMalformedResponseCodeTooLow(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("099 Too low\r\n"));

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testMalformedResponseCodeTooHigh(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("600 Too high\r\n"));

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testMalformedResponseInvalidSeparator(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250|OK\r\n"));

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testMalformedResponseInconsistentMultilineCodes(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250-First\r\n251 Second\r\n"));

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testMalformedResponseStreamClosed(): void
    {
        $handle = new IO\MemoryHandle('');
        $reader = new IO\Reader($handle);

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testResponseCodeBoundary100(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("100 Continue\r\n"));

        $response = parse_reply($reader);

        static::assertSame(100, $response->code);
        static::assertFalse($response->isPositiveCompletion());
        static::assertFalse($response->isPositiveIntermediate());
        static::assertFalse($response->isTransientNegativeCompletion());
        static::assertFalse($response->isPermanentNegativeCompletion());
    }

    public function testResponseCodeBoundary599(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("599 Error\r\n"));

        $response = parse_reply($reader);

        static::assertSame(599, $response->code);
        static::assertTrue($response->isPermanentNegativeCompletion());
    }

    public function testResponseCodeBoundary399(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("399 Intermediate\r\n"));

        $response = parse_reply($reader);

        static::assertSame(399, $response->code);
        static::assertTrue($response->isPositiveIntermediate());
        static::assertFalse($response->isPositiveCompletion());
    }

    public function testResponseCodeBoundary400(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("400 Negative\r\n"));

        $response = parse_reply($reader);

        static::assertSame(400, $response->code);
        static::assertTrue($response->isTransientNegativeCompletion());
        static::assertFalse($response->isPositiveCompletion());
    }

    public function testMultipleResponses(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 First\r\n354 Second\r\n"));

        $first = parse_reply($reader);
        static::assertSame(250, $first->code);
        static::assertSame('First', $first->message);

        $second = parse_reply($reader);
        static::assertSame(354, $second->code);
        static::assertSame('Second', $second->message);
    }

    public function testGreetingResponse(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220 mail.example.com ESMTP Postfix\r\n"));

        $response = parse_reply($reader);

        static::assertSame(220, $response->code);
        static::assertSame('mail.example.com ESMTP Postfix', $response->message);
    }

    public function testMultilineGreeting(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220-mail.example.com ESMTP\r\n220 Service ready\r\n"));

        $response = parse_reply($reader);

        static::assertSame(220, $response->code);
        static::assertStringContainsString('mail.example.com ESMTP', $response->message);
        static::assertStringContainsString('Service ready', $response->message);
    }

    public function testResponseCodeWithLeadingZeros(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("020 Invalid\r\n"));

        $this->expectException(ProtocolException::class);

        parse_reply($reader);
    }

    public function testEnhancedStatusCodeParsed(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 2.1.0 OK\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->class);
        static::assertSame(1, $response->enhancedStatus->subject);
        static::assertSame(0, $response->enhancedStatus->detail);
        static::assertSame('OK', $response->message);
    }

    public function testEnhancedStatusCodePermanentFailure(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.2.1 Mailbox full\r\n"));

        $response = parse_reply($reader);

        static::assertSame(550, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(5, $response->enhancedStatus->class);
        static::assertSame(2, $response->enhancedStatus->subject);
        static::assertSame(1, $response->enhancedStatus->detail);
        static::assertSame('Mailbox full', $response->message);
    }

    public function testEnhancedStatusCodeTransientFailure(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("421 4.3.2 Service not available\r\n"));

        $response = parse_reply($reader);

        static::assertSame(421, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(4, $response->enhancedStatus->class);
        static::assertSame(3, $response->enhancedStatus->subject);
        static::assertSame(2, $response->enhancedStatus->detail);
        static::assertSame('Service not available', $response->message);
    }

    public function testEnhancedStatusCodeWithEmptyMessage(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 2.0.0\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->class);
        static::assertSame(0, $response->enhancedStatus->subject);
        static::assertSame(0, $response->enhancedStatus->detail);
        static::assertSame('', $response->message);
    }

    public function testNoEnhancedStatusCodeForPlainMessage(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 OK\r\n"));

        $response = parse_reply($reader);

        static::assertNull($response->enhancedStatus);
        static::assertSame('OK', $response->message);
    }

    public function testEnhancedStatusCodeUnknownSubjectParsed(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 2.99.0 Custom\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->class);
        static::assertSame(99, $response->enhancedStatus->subject);
        static::assertSame(0, $response->enhancedStatus->detail);
        static::assertSame('Custom', $response->message);
    }

    public function testEnhancedStatusCodeUnknownClassParsed(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 3.1.0 Weird\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(3, $response->enhancedStatus->class);
        static::assertSame(1, $response->enhancedStatus->subject);
        static::assertSame(0, $response->enhancedStatus->detail);
        static::assertSame('Weird', $response->message);
    }

    public function testEnhancedStatusCodeSecuritySubject(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("535 5.7.8 Authentication credentials invalid\r\n"));

        $response = parse_reply($reader);

        static::assertSame(535, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(5, $response->enhancedStatus->class);
        static::assertSame(7, $response->enhancedStatus->subject);
        static::assertSame(8, $response->enhancedStatus->detail);
        static::assertSame('Authentication credentials invalid', $response->message);
    }

    public function testEnhancedStatusCodeMultiDigitDetail(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.1.100 Address rejected\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(100, $response->enhancedStatus->detail);
        static::assertSame('Address rejected', $response->message);
    }

    public function testEnhancedStatusCodeWithLeadingZeros(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 002.001.000 OK\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->class);
        static::assertSame(1, $response->enhancedStatus->subject);
        static::assertSame(0, $response->enhancedStatus->detail);
        static::assertSame('OK', $response->message);
    }

    public function testEnhancedStatusCodeLeadingZeroSubjectAndDetail(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.07.09 Rejected\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(5, $response->enhancedStatus->class);
        static::assertSame(7, $response->enhancedStatus->subject);
        static::assertSame(9, $response->enhancedStatus->detail);
        static::assertSame('Rejected', $response->message);
    }

    public function testEnhancedStatusCodeClass2(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 2.0.0 OK\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertTrue($response->enhancedStatus->isSuccess());
        static::assertFalse($response->enhancedStatus->isPersistentTransientFailure());
        static::assertFalse($response->enhancedStatus->isPermanentFailure());
    }

    public function testEnhancedStatusCodeClass4(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("450 4.2.1 Mailbox busy\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertTrue($response->enhancedStatus->isPersistentTransientFailure());
    }

    public function testEnhancedStatusCodeClass5(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.1.1 User unknown\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertTrue($response->enhancedStatus->isPermanentFailure());
    }

    public function testEnhancedStatusCodeSubject0(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 2.0.0 OK\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(0, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeSubject1(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 2.1.0 Originator OK\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(1, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeSubject2(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.2.1 Mailbox full\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeSubject3(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("421 4.3.2 System not accepting\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(3, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeSubject4(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("452 4.4.5 System congested\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(4, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeSubject5(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.5.0 Protocol error\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(5, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeSubject6(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.6.0 Media error\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(6, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeSubject7(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("535 5.7.8 Auth credentials invalid\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(7, $response->enhancedStatus->subject);
    }

    public function testEnhancedStatusCodeLargeDetail(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 5.1.999 Very specific error\r\n"));

        $response = parse_reply($reader);

        static::assertNotNull($response->enhancedStatus);
        static::assertSame(999, $response->enhancedStatus->detail);
        static::assertSame('Very specific error', $response->message);
    }

    public function testMultilineWithEnhancedStatus(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250-2.0.0 First line\r\n250 Second line\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->class);
    }

    public function testVeryLongResponseLine(): void
    {
        $longMsg = str_repeat('A', 1000);
        $reader = new IO\Reader(new IO\MemoryHandle('250 ' . $longMsg . "\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertSame($longMsg, $response->message);
    }

    public function testResponseWithOnlyCode(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertSame('', $response->message);
        static::assertNull($response->enhancedStatus);
    }

    public function testCode200(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("200 OK\r\n"));

        $response = parse_reply($reader);

        static::assertSame(200, $response->code);
        static::assertTrue($response->isPositiveCompletion());
    }

    public function testCode220(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220 Ready\r\n"));

        $response = parse_reply($reader);

        static::assertSame(220, $response->code);
        static::assertTrue($response->isConnectionsCategory());
    }

    public function testCode221(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("221 Bye\r\n"));

        $response = parse_reply($reader);

        static::assertSame(221, $response->code);
    }

    public function testCode235(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("235 Authentication successful\r\n"));

        $response = parse_reply($reader);

        static::assertSame(235, $response->code);
        static::assertTrue($response->isPositiveCompletion());
    }

    public function testCode334(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("334 VXNlcm5hbWU6\r\n"));

        $response = parse_reply($reader);

        static::assertSame(334, $response->code);
        static::assertTrue($response->isPositiveIntermediate());
    }

    public function testCode354(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("354 End data with .<CR><LF>\r\n"));

        $response = parse_reply($reader);

        static::assertSame(354, $response->code);
        static::assertTrue($response->isPositiveIntermediate());
    }

    public function testCode421(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("421 Service not available\r\n"));

        $response = parse_reply($reader);

        static::assertSame(421, $response->code);
        static::assertTrue($response->isTransientNegativeCompletion());
    }

    public function testCode450(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("450 Mailbox busy\r\n"));

        $response = parse_reply($reader);

        static::assertSame(450, $response->code);
        static::assertTrue($response->isTransientNegativeCompletion());
    }

    public function testCode451(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("451 Requested action aborted\r\n"));

        $response = parse_reply($reader);

        static::assertSame(451, $response->code);
    }

    public function testCode452(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("452 Insufficient storage\r\n"));

        $response = parse_reply($reader);

        static::assertSame(452, $response->code);
    }

    public function testCode500(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("500 Syntax error\r\n"));

        $response = parse_reply($reader);

        static::assertSame(500, $response->code);
        static::assertTrue($response->isPermanentNegativeCompletion());
    }

    public function testCode501(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("501 Syntax error in parameters\r\n"));

        $response = parse_reply($reader);

        static::assertSame(501, $response->code);
    }

    public function testCode502(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("502 Command not implemented\r\n"));

        $response = parse_reply($reader);

        static::assertSame(502, $response->code);
    }

    public function testCode503(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("503 Bad sequence of commands\r\n"));

        $response = parse_reply($reader);

        static::assertSame(503, $response->code);
    }

    public function testCode550(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 Mailbox not found\r\n"));

        $response = parse_reply($reader);

        static::assertSame(550, $response->code);
        static::assertTrue($response->isPermanentNegativeCompletion());
    }

    public function testCode553(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("553 Mailbox name not allowed\r\n"));

        $response = parse_reply($reader);

        static::assertSame(553, $response->code);
    }

    public function testCode554(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("554 Transaction failed\r\n"));

        $response = parse_reply($reader);

        static::assertSame(554, $response->code);
    }

    public function testMultilineEmptyMiddleLine(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250-First\r\n250-\r\n250 Last\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertStringContainsString('First', $response->message);
        static::assertStringContainsString('Last', $response->message);
    }

    public function testThreeLineResponse(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250-Line1\r\n250-Line2\r\n250 Line3\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertSame("Line1\nLine2\nLine3", $response->message);
    }

    public function testResponseWithCarriageReturnInLine(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 OK\r\n"));

        $response = parse_reply($reader);

        static::assertSame(250, $response->code);
        static::assertSame('OK', $response->message);
    }
}
