<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Multipart;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\MIME\ContentDisposition;
use Psl\MIME\Headers;
use Psl\MIME\MultiPart\Composite;
use Psl\MIME\MultiPart\Parser;
use Psl\MIME\Part\Part;

use function iterator_to_array;

final class RoundTripTest extends TestCase
{
    public function testGenerateThenParse(): void
    {
        $mainPart = new Part(Headers::fromPairs([
            ['Content-Type',        'text/plain'],
            ['Content-Disposition', 'form-data; name="field1"'],
        ]), new IO\MemoryHandle('value1'));

        $mixed = new Composite($mainPart, 'test-boundary');
        $mixed->addPart(
            new Part(Headers::fromPairs([[
                'Content-Type',
                'application/json',
            ]]), new IO\MemoryHandle('{"key":"value"}')),
        );

        $output = new IO\MemoryHandle();
        IO\copy($mixed->body(), $output);

        $parser = new Parser('test-boundary');
        $output->seek(0);
        $parts = iterator_to_array($parser->parse($output));

        static::assertCount(2, $parts);

        static::assertSame('text/plain', $parts[0]->mediaType->essence());
        static::assertSame('value1', $parts[0]->body->readAll());
        $dispositionValue = $parts[0]->headers->get('content-disposition');
        static::assertNotNull($dispositionValue);
        $disposition = ContentDisposition::parse($dispositionValue);
        static::assertSame('field1', $disposition->parameters->get('name'));

        static::assertSame('application/json', $parts[1]->mediaType->essence());
        static::assertSame('{"key":"value"}', $parts[1]->body->readAll());
    }

    public function testRoundTripWithEmptyPart(): void
    {
        $main = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle(''));
        $mixed = new Composite($main, 'boundary');

        $output = new IO\MemoryHandle();
        IO\copy($mixed->body(), $output);

        $parser = new Parser('boundary');
        $output->seek(0);
        $parts = iterator_to_array($parser->parse($output));

        static::assertCount(1, $parts);
        static::assertSame('', $parts[0]->body->readAll());
    }

    public function testRoundTripWithBinaryContent(): void
    {
        $binaryData = "\x00\x01\x02\xFF\xFE\xFD";

        $main = new Part(Headers::fromPairs([[
            'Content-Type',
            'application/octet-stream',
        ]]), new IO\MemoryHandle($binaryData));
        $mixed = new Composite($main, 'boundary');

        $output = new IO\MemoryHandle();
        IO\copy($mixed->body(), $output);

        $parser = new Parser('boundary');
        $output->seek(0);
        $parts = iterator_to_array($parser->parse($output));

        static::assertCount(1, $parts);
        static::assertSame($binaryData, $parts[0]->body->readAll());
    }

    public function testRoundTripMultiplePartsPreservesOrder(): void
    {
        $main = new Part(Headers::fromPairs([['X-Index', '0']]), new IO\MemoryHandle('part-0'));
        $mixed = new Composite($main, 'boundary');

        for ($i = 1; $i < 5; $i++) {
            $mixed->addPart(
                new Part(Headers::fromPairs([['X-Index', (string) $i]]), new IO\MemoryHandle('part-' . $i)),
            );
        }

        $output = new IO\MemoryHandle();
        IO\copy($mixed->body(), $output);

        $parser = new Parser('boundary');
        $output->seek(0);
        $parts = iterator_to_array($parser->parse($output));

        static::assertCount(5, $parts);
        for ($i = 0; $i < 5; $i++) {
            static::assertSame('part-' . $i, $parts[$i]->body->readAll());
        }
    }
}
