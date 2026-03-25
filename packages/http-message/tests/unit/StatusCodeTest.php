<?php

declare(strict_types=1);

namespace Psl\HTTP\Message\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Message;

final class StatusCodeTest extends TestCase
{
    public function testInformational(): void
    {
        static::assertSame(100, Message\STATUS_CONTINUE);
        static::assertSame(101, Message\STATUS_SWITCHING_PROTOCOLS);
        static::assertSame(103, Message\STATUS_EARLY_HINTS);
    }

    public function testSuccessful(): void
    {
        static::assertSame(200, Message\STATUS_OK);
        static::assertSame(201, Message\STATUS_CREATED);
        static::assertSame(204, Message\STATUS_NO_CONTENT);
    }

    public function testRedirection(): void
    {
        static::assertSame(301, Message\STATUS_MOVED_PERMANENTLY);
        static::assertSame(302, Message\STATUS_FOUND);
        static::assertSame(304, Message\STATUS_NOT_MODIFIED);
        static::assertSame(307, Message\STATUS_TEMPORARY_REDIRECT);
        static::assertSame(308, Message\STATUS_PERMANENT_REDIRECT);
    }

    public function testClientError(): void
    {
        static::assertSame(400, Message\STATUS_BAD_REQUEST);
        static::assertSame(401, Message\STATUS_UNAUTHORIZED);
        static::assertSame(403, Message\STATUS_FORBIDDEN);
        static::assertSame(404, Message\STATUS_NOT_FOUND);
        static::assertSame(405, Message\STATUS_METHOD_NOT_ALLOWED);
        static::assertSame(418, Message\STATUS_IM_A_TEAPOT);
        static::assertSame(429, Message\STATUS_TOO_MANY_REQUESTS);
        static::assertSame(451, Message\STATUS_UNAVAILABLE_FOR_LEGAL_REASONS);
    }

    public function testServerError(): void
    {
        static::assertSame(500, Message\STATUS_INTERNAL_SERVER_ERROR);
        static::assertSame(502, Message\STATUS_BAD_GATEWAY);
        static::assertSame(503, Message\STATUS_SERVICE_UNAVAILABLE);
        static::assertSame(504, Message\STATUS_GATEWAY_TIMEOUT);
    }
}
