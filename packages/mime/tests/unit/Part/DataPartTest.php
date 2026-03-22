<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Part;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\IO;
use Psl\MIME\ContentId;
use Psl\MIME\MediaType;
use Psl\MIME\Part\Data;

final class DataPartTest extends TestCase
{
    public function testConstructDefaults(): void
    {
        $part = new Data(new IO\MemoryHandle('binary data'));

        static::assertSame('application/octet-stream', $part->mediaType->essence());
        static::assertSame('attachment', $part->disposition->type);
        static::assertNull($part->filename);
        static::assertNull($part->contentId);
    }

    public function testConstructWithFilename(): void
    {
        $part = new Data(new IO\MemoryHandle('data'), 'report.pdf');

        static::assertSame('report.pdf', $part->filename);
        static::assertSame('report.pdf', $part->disposition->filename());
    }

    public function testConstructWithMediaType(): void
    {
        $type = MediaType::parse('image/png');
        $part = new Data(new IO\MemoryHandle('data'), mediaType: $type);

        static::assertSame('image/png', $part->mediaType->essence());
    }

    public function testAsInline(): void
    {
        $part = new Data(new IO\MemoryHandle('data'), 'image.png', MediaType::parse('image/png'));
        $id = ContentId::generate();
        $inline = $part->asInline($id);

        static::assertSame('inline', $inline->disposition->type);
        static::assertNotNull($inline->contentId);
        static::assertSame($id->id, $inline->contentId->id);
        static::assertSame('image.png', $inline->filename);
        static::assertSame('image/png', $inline->mediaType->essence());
    }

    public function testToPartHeaders(): void
    {
        $part = new Data(new IO\MemoryHandle('test'), 'file.bin');

        $headerMap = [];
        foreach ($part->headers->pairs() as [$name, $value]) {
            $headerMap[$name] = $value;
        }

        static::assertArrayHasKey('Content-Type', $headerMap);
        static::assertArrayHasKey('Content-Disposition', $headerMap);
        static::assertSame('base64', $headerMap['Content-Transfer-Encoding']);
        static::assertArrayNotHasKey('Content-ID', $headerMap);
    }

    public function testToPartWithContentId(): void
    {
        $id = ContentId::generate();
        $part = new Data(new IO\MemoryHandle('test'), 'img.png', contentId: $id);

        $headerMap = [];
        foreach ($part->headers->pairs() as [$name, $value]) {
            $headerMap[$name] = $value;
        }

        static::assertSame($id->toString(), $headerMap['Content-ID']);
    }

    public function testToPartBodyIsBase64Encoded(): void
    {
        $data = 'Hello, binary world!';
        $part = new Data(new IO\MemoryHandle($data));

        $encoded = $part->body()->readAll();
        $decoded = Base64\decode(\trim($encoded));
        static::assertSame($data, $decoded);
    }
}
