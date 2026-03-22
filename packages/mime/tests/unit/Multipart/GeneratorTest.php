<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Multipart;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\MIME\Headers;
use Psl\MIME\MultiPart\Alternative;
use Psl\MIME\MultiPart\Composite;
use Psl\MIME\Part\Part;

use function substr_count;

final class GeneratorTest extends TestCase
{
    public function testSinglePart(): void
    {
        $main = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('Hello'));
        $mixed = new Composite($main, 'boundary');

        $result = $mixed->body()->readAll();

        static::assertStringContainsString('--boundary', $result);
        static::assertStringContainsString('Content-Type: text/plain', $result);
        static::assertStringContainsString('Hello', $result);
        static::assertStringContainsString('--boundary--', $result);
    }

    public function testMultipleParts(): void
    {
        $alt = new Alternative('boundary');
        $alt->addPart(new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('Part 1')));
        $alt->addPart(
            new Part(Headers::fromPairs([['Content-Type', 'text/html']]), new IO\MemoryHandle('<b>Part 2</b>')),
        );

        $result = $alt->body()->readAll();

        static::assertStringContainsString('Part 1', $result);
        static::assertStringContainsString('<b>Part 2</b>', $result);
        static::assertSame(2, substr_count($result, '--boundary' . "\r\n"));
        static::assertStringContainsString('--boundary--', $result);
    }

    public function testEmptyBody(): void
    {
        $main = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle(''));
        $mixed = new Composite($main, 'boundary');

        $result = $mixed->body()->readAll();

        static::assertStringContainsString("--boundary\r\n", $result);
        static::assertStringContainsString("--boundary--\r\n", $result);
    }

    public function testMultipleHeaders(): void
    {
        $main = new Part(Headers::fromPairs([
            ['Content-Type',        'text/plain'],
            ['Content-Disposition', 'form-data; name="field"'],
        ]), new IO\MemoryHandle('value'));
        $mixed = new Composite($main, 'boundary');

        $result = $mixed->body()->readAll();

        static::assertStringContainsString('Content-Type: text/plain', $result);
        static::assertStringContainsString('Content-Disposition: form-data; name="field"', $result);
    }
}
