<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\EnhancedStatusCode;
use Psl\SMTP\Reply;
use Stringable;

use function str_repeat;

final class ReplyTest extends TestCase
{
    public function testPositiveCompletionResponse(): void
    {
        $response = new Reply(250, null, 'OK');

        static::assertSame(250, $response->code);
        static::assertNull($response->enhancedStatus);
        static::assertSame('OK', $response->message);
        static::assertTrue($response->isPositiveCompletion());
        static::assertFalse($response->isPositiveIntermediate());
        static::assertFalse($response->isTransientNegativeCompletion());
        static::assertFalse($response->isPermanentNegativeCompletion());
    }

    public function testPositiveIntermediateResponse(): void
    {
        $response = new Reply(354, null, 'Go ahead');

        static::assertTrue($response->isPositiveIntermediate());
        static::assertFalse($response->isPositiveCompletion());
    }

    public function testTransientNegativeResponse(): void
    {
        $response = new Reply(421, null, 'Service not available');

        static::assertTrue($response->isTransientNegativeCompletion());
        static::assertFalse($response->isPositiveCompletion());
    }

    public function testPermanentNegativeResponse(): void
    {
        $response = new Reply(550, null, 'User not found');

        static::assertTrue($response->isPermanentNegativeCompletion());
        static::assertFalse($response->isPositiveCompletion());
    }

    public function testSyntaxCategory(): void
    {
        $response = new Reply(500, null, 'Syntax error');

        static::assertTrue($response->isSyntaxCategory());
        static::assertFalse($response->isInformationCategory());
    }

    public function testInformationCategory(): void
    {
        $response = new Reply(211, null, 'System status');

        static::assertTrue($response->isInformationCategory());
        static::assertFalse($response->isSyntaxCategory());
    }

    public function testConnectionsCategory(): void
    {
        $response = new Reply(220, null, 'mail.example.com ESMTP');

        static::assertTrue($response->isConnectionsCategory());
    }

    public function testUnspecifiedCategory(): void
    {
        $response = new Reply(334, null, 'Challenge');

        static::assertTrue($response->isUnspecifiedCategory());

        $response = new Reply(443, null, 'Unspecified');

        static::assertTrue($response->isUnspecifiedCategory());
    }

    public function testMailSystemCategory(): void
    {
        $response = new Reply(250, null, 'OK');

        static::assertTrue($response->isMailSystemCategory());
    }

    public function testToStringWithoutEnhancedStatus(): void
    {
        $response = new Reply(220, null, 'mail.example.com ESMTP');

        static::assertSame('220 mail.example.com ESMTP', $response->toString());
        static::assertSame('220 mail.example.com ESMTP', (string) $response);
    }

    public function testWithEnhancedStatus(): void
    {
        $enhanced = new EnhancedStatusCode(2, 1, 0);

        $response = new Reply(250, $enhanced, 'OK');

        static::assertSame(250, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->class);
        static::assertSame(1, $response->enhancedStatus->subject);
        static::assertSame(0, $response->enhancedStatus->detail);
        static::assertSame('OK', $response->message);
        static::assertTrue($response->isPositiveCompletion());
    }

    public function testToStringWithEnhancedStatus(): void
    {
        $enhanced = new EnhancedStatusCode(5, 2, 1);

        $response = new Reply(550, $enhanced, 'User unknown');

        static::assertSame('550 5.2.1 User unknown', $response->toString());
        static::assertSame('550 5.2.1 User unknown', (string) $response);
    }

    public function testEnhancedStatusCodeToString(): void
    {
        $enhanced = new EnhancedStatusCode(4, 3, 2);

        static::assertSame('4.3.2', $enhanced->toString());
        static::assertSame('4.3.2', (string) $enhanced);
    }

    public function testEnhancedStatusCodeIsSuccess(): void
    {
        $enhanced = new EnhancedStatusCode(2, 0, 0);

        static::assertTrue($enhanced->isSuccess());
        static::assertFalse($enhanced->isPersistentTransientFailure());
        static::assertFalse($enhanced->isPermanentFailure());
    }

    public function testEnhancedStatusCodeIsPersistentTransientFailure(): void
    {
        $enhanced = new EnhancedStatusCode(4, 3, 2);

        static::assertTrue($enhanced->isPersistentTransientFailure());
        static::assertFalse($enhanced->isSuccess());
        static::assertFalse($enhanced->isPermanentFailure());
    }

    public function testEnhancedStatusCodeIsPermanentFailure(): void
    {
        $enhanced = new EnhancedStatusCode(5, 7, 8);

        static::assertTrue($enhanced->isPermanentFailure());
        static::assertFalse($enhanced->isSuccess());
        static::assertFalse($enhanced->isPersistentTransientFailure());
    }

    public function testCode100IsNotAnyStandardCategory(): void
    {
        $response = new Reply(100, null, 'Continue');

        static::assertFalse($response->isPositiveCompletion());
        static::assertFalse($response->isPositiveIntermediate());
        static::assertFalse($response->isTransientNegativeCompletion());
        static::assertFalse($response->isPermanentNegativeCompletion());
    }

    public function testCode199(): void
    {
        $response = new Reply(199, null, 'Unknown');

        static::assertFalse($response->isPositiveCompletion());
        static::assertFalse($response->isPositiveIntermediate());
        static::assertFalse($response->isTransientNegativeCompletion());
        static::assertFalse($response->isPermanentNegativeCompletion());
    }

    public function testCode200(): void
    {
        $response = new Reply(200, null, 'OK');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isSyntaxCategory());
    }

    public function testCode211IsInformationCategory(): void
    {
        $response = new Reply(211, null, 'System status');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isInformationCategory());
        static::assertFalse($response->isSyntaxCategory());
        static::assertFalse($response->isConnectionsCategory());
        static::assertFalse($response->isUnspecifiedCategory());
        static::assertFalse($response->isMailSystemCategory());
    }

    public function testCode220IsConnectionsCategory(): void
    {
        $response = new Reply(220, null, 'Ready');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isConnectionsCategory());
        static::assertFalse($response->isSyntaxCategory());
        static::assertFalse($response->isInformationCategory());
        static::assertFalse($response->isUnspecifiedCategory());
        static::assertFalse($response->isMailSystemCategory());
    }

    public function testCode230IsUnspecifiedCategory(): void
    {
        $response = new Reply(230, null, 'Test');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isUnspecifiedCategory());
    }

    public function testCode240IsUnspecifiedCategory(): void
    {
        $response = new Reply(240, null, 'Test');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isUnspecifiedCategory());
    }

    public function testCode250IsMailSystemCategory(): void
    {
        $response = new Reply(250, null, 'OK');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isMailSystemCategory());
        static::assertFalse($response->isSyntaxCategory());
        static::assertFalse($response->isInformationCategory());
        static::assertFalse($response->isConnectionsCategory());
        static::assertFalse($response->isUnspecifiedCategory());
    }

    public function testCode299(): void
    {
        $response = new Reply(299, null, 'Custom');

        static::assertTrue($response->isPositiveCompletion());
    }

    public function testCode300IsPositiveIntermediate(): void
    {
        $response = new Reply(300, null, 'Intermediate');

        static::assertTrue($response->isPositiveIntermediate());
        static::assertFalse($response->isPositiveCompletion());
    }

    public function testCode354(): void
    {
        $response = new Reply(354, null, 'Start mail input');

        static::assertTrue($response->isPositiveIntermediate());
        static::assertTrue($response->isMailSystemCategory());
    }

    public function testCode400IsTransientNegative(): void
    {
        $response = new Reply(400, null, 'Error');

        static::assertTrue($response->isTransientNegativeCompletion());
        static::assertFalse($response->isPermanentNegativeCompletion());
    }

    public function testCode421(): void
    {
        $response = new Reply(421, null, 'Service not available');

        static::assertTrue($response->isTransientNegativeCompletion());
        static::assertTrue($response->isConnectionsCategory());
    }

    public function testCode450(): void
    {
        $response = new Reply(450, null, 'Mailbox unavailable');

        static::assertTrue($response->isTransientNegativeCompletion());
        static::assertTrue($response->isMailSystemCategory());
    }

    public function testCode500IsPermanentNegative(): void
    {
        $response = new Reply(500, null, 'Syntax error');

        static::assertTrue($response->isPermanentNegativeCompletion());
        static::assertTrue($response->isSyntaxCategory());
        static::assertFalse($response->isTransientNegativeCompletion());
    }

    public function testCode502(): void
    {
        $response = new Reply(502, null, 'Command not implemented');

        static::assertTrue($response->isPermanentNegativeCompletion());
        static::assertTrue($response->isSyntaxCategory());
    }

    public function testCode535(): void
    {
        $response = new Reply(535, null, 'Authentication failed');

        static::assertTrue($response->isPermanentNegativeCompletion());
        static::assertTrue($response->isUnspecifiedCategory());
    }

    public function testCode550(): void
    {
        $response = new Reply(550, null, 'User not found');

        static::assertTrue($response->isPermanentNegativeCompletion());
        static::assertTrue($response->isMailSystemCategory());
    }

    public function testCode599(): void
    {
        $response = new Reply(599, null, 'Error');

        static::assertTrue($response->isPermanentNegativeCompletion());
    }

    public function testToStringEmptyMessage(): void
    {
        $response = new Reply(250, null, '');

        static::assertSame('250 ', $response->toString());
    }

    public function testToStringLongMessage(): void
    {
        $longMessage = str_repeat('A', 500);
        $response = new Reply(250, null, $longMessage);

        static::assertSame('250 ' . $longMessage, $response->toString());
    }

    public function testToStringMessageWithSpecialCharacters(): void
    {
        $response = new Reply(250, null, 'OK <user@example.com>');

        static::assertSame('250 OK <user@example.com>', $response->toString());
    }

    public function testToStringMessageWithNewlines(): void
    {
        $response = new Reply(250, null, "Line1\nLine2\nLine3");

        static::assertSame("250 Line1\nLine2\nLine3", $response->toString());
    }

    public function testToStringWithEnhancedStatusAndEmptyMessage(): void
    {
        $enhanced = new EnhancedStatusCode(2, 0, 0);
        $response = new Reply(250, $enhanced, '');

        static::assertSame('250 2.0.0 ', $response->toString());
    }

    public function testToStringWithEnhancedStatusLongMessage(): void
    {
        $enhanced = new EnhancedStatusCode(5, 1, 1);
        $longMsg = str_repeat('X', 200);
        $response = new Reply(550, $enhanced, $longMsg);

        static::assertSame('550 5.1.1 ' . $longMsg, $response->toString());
    }

    public function testMagicToStringMatchesToString(): void
    {
        $response = new Reply(250, null, 'OK');

        static::assertSame($response->toString(), (string) $response);
    }

    public function testMagicToStringMatchesToStringWithEnhanced(): void
    {
        $enhanced = new EnhancedStatusCode(2, 1, 0);
        $response = new Reply(250, $enhanced, 'Sender OK');

        static::assertSame($response->toString(), (string) $response);
    }

    public function testPositiveCompletionAndSyntaxCategory(): void
    {
        $response = new Reply(200, null, 'OK');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isSyntaxCategory());
    }

    public function testPositiveCompletionAndConnectionsCategory(): void
    {
        $response = new Reply(221, null, 'Bye');

        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isConnectionsCategory());
    }

    public function testTransientNegativeAndMailSystemCategory(): void
    {
        $response = new Reply(450, null, 'Mailbox busy');

        static::assertTrue($response->isTransientNegativeCompletion());
        static::assertTrue($response->isMailSystemCategory());
    }

    public function testPermanentNegativeAndInformationCategory(): void
    {
        $response = new Reply(510, null, 'Bad email');

        static::assertTrue($response->isPermanentNegativeCompletion());
        static::assertTrue($response->isInformationCategory());
    }

    public function testReadonlyProperties(): void
    {
        $enhanced = new EnhancedStatusCode(2, 0, 0);
        $response = new Reply(250, $enhanced, 'OK');

        static::assertSame(250, $response->code);
        static::assertSame($enhanced, $response->enhancedStatus);
        static::assertSame('OK', $response->message);
    }

    public function testStringableInterface(): void
    {
        $response = new Reply(250, null, 'OK');

        static::assertInstanceOf(Stringable::class, $response);
    }

    public function testIsSyntaxCategoryCode199IsNotSyntax(): void
    {
        $response = new Reply(199, null, 'Test');

        static::assertFalse($response->isSyntaxCategory());
    }

    public function testIsSyntaxCategoryCode209IsSyntax(): void
    {
        $response = new Reply(209, null, 'Test');

        static::assertTrue($response->isSyntaxCategory());
    }

    public function testIsInformationCategoryCode519IsInformation(): void
    {
        $response = new Reply(519, null, 'Test');

        static::assertTrue($response->isInformationCategory());
    }

    public function testIsInformationCategoryCode219IsInformation(): void
    {
        $response = new Reply(219, null, 'Test');

        static::assertTrue($response->isInformationCategory());
    }

    public function testIsConnectionsCategoryCode529IsConnections(): void
    {
        $response = new Reply(529, null, 'Test');

        static::assertTrue($response->isConnectionsCategory());
    }

    public function testIsConnectionsCategoryCode229IsConnections(): void
    {
        $response = new Reply(229, null, 'Test');

        static::assertTrue($response->isConnectionsCategory());
    }

    public function testIsUnspecifiedCategoryCode249IsUnspecified(): void
    {
        $response = new Reply(249, null, 'Test');

        static::assertTrue($response->isUnspecifiedCategory());
    }

    public function testIsUnspecifiedCategoryCode249WithDivMutation(): void
    {
        $response = new Reply(249, null, 'Test');

        static::assertTrue($response->isUnspecifiedCategory());
    }

    public function testIsMailSystemCategoryCode559IsMailSystem(): void
    {
        $response = new Reply(559, null, 'Test');

        static::assertTrue($response->isMailSystemCategory());
    }
}
