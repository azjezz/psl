<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\SMTP\EnhancedStatusCode;
use Stringable;

final class EnhancedStatusCodeTest extends TestCase
{
    public function testConstructorBasic(): void
    {
        $code = new EnhancedStatusCode(2, 1, 0);

        static::assertSame(2, $code->class);
        static::assertSame(1, $code->subject);
        static::assertSame(0, $code->detail);
    }

    public function testConstructorWithZeros(): void
    {
        $code = new EnhancedStatusCode(0, 0, 0);

        static::assertSame(0, $code->class);
        static::assertSame(0, $code->subject);
        static::assertSame(0, $code->detail);
    }

    public function testConstructorWithLargeValues(): void
    {
        $code = new EnhancedStatusCode(999, 999, 999);

        static::assertSame(999, $code->class);
        static::assertSame(999, $code->subject);
        static::assertSame(999, $code->detail);
    }

    public function testIsSuccessWithClass2(): void
    {
        $code = new EnhancedStatusCode(2, 0, 0);

        static::assertTrue($code->isSuccess());
        static::assertFalse($code->isPersistentTransientFailure());
        static::assertFalse($code->isPermanentFailure());
    }

    public function testIsSuccessWithDifferentSubjectAndDetail(): void
    {
        $code = new EnhancedStatusCode(2, 7, 99);

        static::assertTrue($code->isSuccess());
    }

    public function testIsPersistentTransientFailureWithClass4(): void
    {
        $code = new EnhancedStatusCode(4, 0, 0);

        static::assertTrue($code->isPersistentTransientFailure());
        static::assertFalse($code->isSuccess());
        static::assertFalse($code->isPermanentFailure());
    }

    public function testIsPersistentTransientFailureWithDifferentSubjectAndDetail(): void
    {
        $code = new EnhancedStatusCode(4, 3, 2);

        static::assertTrue($code->isPersistentTransientFailure());
    }

    public function testIsPermanentFailureWithClass5(): void
    {
        $code = new EnhancedStatusCode(5, 0, 0);

        static::assertTrue($code->isPermanentFailure());
        static::assertFalse($code->isSuccess());
        static::assertFalse($code->isPersistentTransientFailure());
    }

    public function testIsPermanentFailureWithDifferentSubjectAndDetail(): void
    {
        $code = new EnhancedStatusCode(5, 7, 8);

        static::assertTrue($code->isPermanentFailure());
    }

    public function testClass0IsNeitherSuccessNorFailure(): void
    {
        $code = new EnhancedStatusCode(0, 0, 0);

        static::assertFalse($code->isSuccess());
        static::assertFalse($code->isPersistentTransientFailure());
        static::assertFalse($code->isPermanentFailure());
    }

    public function testClass1IsNeitherSuccessNorFailure(): void
    {
        $code = new EnhancedStatusCode(1, 0, 0);

        static::assertFalse($code->isSuccess());
        static::assertFalse($code->isPersistentTransientFailure());
        static::assertFalse($code->isPermanentFailure());
    }

    public function testClass3IsNeitherSuccessNorFailure(): void
    {
        $code = new EnhancedStatusCode(3, 0, 0);

        static::assertFalse($code->isSuccess());
        static::assertFalse($code->isPersistentTransientFailure());
        static::assertFalse($code->isPermanentFailure());
    }

    public function testClass6IsNeitherSuccessNorFailure(): void
    {
        $code = new EnhancedStatusCode(6, 0, 0);

        static::assertFalse($code->isSuccess());
        static::assertFalse($code->isPersistentTransientFailure());
        static::assertFalse($code->isPermanentFailure());
    }

    public function testClass999IsNeitherSuccessNorFailure(): void
    {
        $code = new EnhancedStatusCode(999, 0, 0);

        static::assertFalse($code->isSuccess());
        static::assertFalse($code->isPersistentTransientFailure());
        static::assertFalse($code->isPermanentFailure());
    }

    public function testToStringBasic(): void
    {
        $code = new EnhancedStatusCode(2, 1, 0);

        static::assertSame('2.1.0', $code->toString());
    }

    public function testToStringWithZeros(): void
    {
        $code = new EnhancedStatusCode(0, 0, 0);

        static::assertSame('0.0.0', $code->toString());
    }

    public function testToStringWithLargeValues(): void
    {
        $code = new EnhancedStatusCode(999, 999, 999);

        static::assertSame('999.999.999', $code->toString());
    }

    public function testToStringWithMixedValues(): void
    {
        $code = new EnhancedStatusCode(5, 7, 8);

        static::assertSame('5.7.8', $code->toString());
    }

    public function testStringableInterface(): void
    {
        $code = new EnhancedStatusCode(2, 1, 0);

        static::assertInstanceOf(Stringable::class, $code);
        static::assertSame('2.1.0', (string) $code);
    }

    public function testToStringAndMagicToStringAreIdentical(): void
    {
        $code = new EnhancedStatusCode(4, 3, 2);

        static::assertSame($code->toString(), $code->__toString());
    }

    #[DataProvider('subjectProvider')]
    public function testVariousSubjectValues(int $subject): void
    {
        $code = new EnhancedStatusCode(2, $subject, 0);

        static::assertSame($subject, $code->subject);
        static::assertSame('2.' . $subject . '.0', $code->toString());
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function subjectProvider(): iterable
    {
        yield 'subject 0 - Other/Undefined' => [0];
        yield 'subject 1 - Addressing' => [1];
        yield 'subject 2 - Mailbox' => [2];
        yield 'subject 3 - Mail system' => [3];
        yield 'subject 4 - Network/Routing' => [4];
        yield 'subject 5 - Mail delivery protocol' => [5];
        yield 'subject 6 - Message content/media' => [6];
        yield 'subject 7 - Security/Policy' => [7];
        yield 'subject 8 - Beyond standard' => [8];
        yield 'subject 99 - High value' => [99];
        yield 'subject 100 - Triple digit' => [100];
    }

    #[DataProvider('detailProvider')]
    public function testVariousDetailValues(int $detail): void
    {
        $code = new EnhancedStatusCode(5, 1, $detail);

        static::assertSame($detail, $code->detail);
        static::assertSame('5.1.' . $detail, $code->toString());
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function detailProvider(): iterable
    {
        yield 'detail 0' => [0];
        yield 'detail 1' => [1];
        yield 'detail 99' => [99];
        yield 'detail 100' => [100];
        yield 'detail 999' => [999];
    }

    #[DataProvider('classMethodCombinationProvider')]
    public function testAllClassMethodCombinations(
        int $class,
        bool $isSuccess,
        bool $isTransient,
        bool $isPermanent,
    ): void {
        $code = new EnhancedStatusCode($class, 0, 0);

        static::assertSame($isSuccess, $code->isSuccess());
        static::assertSame($isTransient, $code->isPersistentTransientFailure());
        static::assertSame($isPermanent, $code->isPermanentFailure());
    }

    /**
     * @return iterable<string, array{int, bool, bool, bool}>
     */
    public static function classMethodCombinationProvider(): iterable
    {
        yield 'class 0' => [0, false, false, false];
        yield 'class 1' => [1, false, false, false];
        yield 'class 2 (success)' => [2, true, false, false];
        yield 'class 3' => [3, false, false, false];
        yield 'class 4 (transient)' => [4, false, true, false];
        yield 'class 5 (permanent)' => [5, false, false, true];
        yield 'class 6' => [6, false, false, false];
        yield 'class 7' => [7, false, false, false];
        yield 'class 8' => [8, false, false, false];
        yield 'class 9' => [9, false, false, false];
        yield 'class 10' => [10, false, false, false];
        yield 'class 99' => [99, false, false, false];
    }

    public function testCommonSmtpStatusCodes(): void
    {
        // 2.0.0 success
        $code = new EnhancedStatusCode(2, 0, 0);
        static::assertTrue($code->isSuccess());
        static::assertSame('2.0.0', $code->toString());

        // 2.1.0 originator address valid
        $code = new EnhancedStatusCode(2, 1, 0);
        static::assertTrue($code->isSuccess());
        static::assertSame('2.1.0', $code->toString());

        // 2.1.5 destination address valid
        $code = new EnhancedStatusCode(2, 1, 5);
        static::assertTrue($code->isSuccess());
        static::assertSame('2.1.5', $code->toString());

        // 4.3.2 system not accepting messages
        $code = new EnhancedStatusCode(4, 3, 2);
        static::assertTrue($code->isPersistentTransientFailure());
        static::assertSame('4.3.2', $code->toString());

        // 5.1.1 bad destination mailbox
        $code = new EnhancedStatusCode(5, 1, 1);
        static::assertTrue($code->isPermanentFailure());
        static::assertSame('5.1.1', $code->toString());

        // 5.7.8 authentication credentials invalid
        $code = new EnhancedStatusCode(5, 7, 8);
        static::assertTrue($code->isPermanentFailure());
        static::assertSame('5.7.8', $code->toString());
    }

    public function testReadonlyProperties(): void
    {
        $code = new EnhancedStatusCode(2, 1, 0);

        static::assertSame(2, $code->class);
        static::assertSame(1, $code->subject);
        static::assertSame(0, $code->detail);
    }
}
