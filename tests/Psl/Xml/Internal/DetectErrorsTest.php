<?php

declare(strict_types=1);

namespace Psl\Tests\Xml\Internal;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Xml\Internal;

use function libxml_use_internal_errors;
use function simplexml_load_string;

final class DetectErrorsTest extends TestCase
{
    public function testItCanContinueWhenNoErrorsAreDetects(): void
    {
        [$result, $errors] = Internal\detect_errors(
            static function (): string {
                self::assertTrue(libxml_use_internal_errors());

                return 'ok';
            }
        );

        static::assertTrue($result->isSucceeded());
        static::assertSame('ok', $result->getResult());
        static::assertCount(0, $errors);
    }

    public function testItCanDetectXmlErrorsInsideCallableAndReturnOk(): void
    {
        [$result, $errors] = Internal\detect_errors(
            static function (): string {
                simplexml_load_string('<notvalidxml');

                return 'ok';
            }
        );

        static::assertTrue($result->isSucceeded());
        static::assertSame('ok', $result->getResult());
        static::assertCount(1, $errors);
    }

    public function testItCanDetectXmlErrorsInsideCallableAndReturnAFailure(): void
    {
        $exception = new Exception('nonono');
        [$result, $errors] = Internal\detect_errors(
            static function () use ($exception) {
                simplexml_load_string('<notvalidxml');

                throw $exception;
            }
        );

        static::assertTrue($result->isFailed());
        static::assertSame($exception, $result->getException());
        static::assertCount(1, $errors);
    }

    public function testItDoesNotUsePreviouslyOccuredExceptions(): void
    {
        libxml_use_internal_errors(true);
        simplexml_load_string('<notvalidxml');

        [$result, $errors] = Internal\detect_errors(
            static function (): string {
                simplexml_load_string('<notvalidxml');

                return 'ok';
            }
        );

        static::assertTrue($result->isSucceeded());
        static::assertSame('ok', $result->getResult());
        static::assertCount(1, $errors);
    }

    public function testItCanUseInternalXmlErrorsDuringAFunctionCall(): void
    {
        libxml_use_internal_errors(false);

        [$result, $errors] = Internal\detect_errors(
            static function () {
                self::assertTrue(libxml_use_internal_errors());

                return 'ok';
            }
        );

        static::assertFalse(libxml_use_internal_errors());
        static::assertTrue($result->isSucceeded());
        static::assertSame('ok', $result->getResult());
        static::assertCount(0, $errors);
    }
}
