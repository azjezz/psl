<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Internal\Registry\Types;
use Psl\MIME\MediaType;
use Psl\Str\Byte;

final class RegistryTest extends TestCase
{
    public function testFromExtensionHtml(): void
    {
        $type = MediaType::fromExtension('html');

        static::assertNotNull($type);
        static::assertSame('text/html', $type->essence());
    }

    public function testFromExtensionJson(): void
    {
        $type = MediaType::fromExtension('json');

        static::assertNotNull($type);
        static::assertSame('application/json', $type->essence());
    }

    public function testFromExtensionPng(): void
    {
        $type = MediaType::fromExtension('png');

        static::assertNotNull($type);
        static::assertSame('image/png', $type->essence());
    }

    public function testFromExtensionCaseInsensitive(): void
    {
        $type = MediaType::fromExtension('JSON');

        static::assertNotNull($type);
        static::assertSame('application/json', $type->essence());
    }

    public function testFromExtensionUnknown(): void
    {
        static::assertNull(MediaType::fromExtension('zzzzz'));
    }

    public function testExtensionsForHtml(): void
    {
        $type = new MediaType('text', 'html');
        $extensions = $type->extensions();

        static::assertContains('html', $extensions);
        static::assertContains('htm', $extensions);
    }

    public function testExtensionsForJson(): void
    {
        $type = new MediaType('application', 'json');
        $extensions = $type->extensions();

        static::assertContains('json', $extensions);
    }

    public function testExtensionsForUnknown(): void
    {
        $type = new MediaType('application', 'x-totally-custom-unknown');
        $extensions = $type->extensions();

        static::assertSame([], $extensions);
    }

    public function testIsRegisteredJson(): void
    {
        $type = new MediaType('application', 'json');

        static::assertTrue($type->isRegistered());
    }

    public function testIsRegisteredHtml(): void
    {
        $type = new MediaType('text', 'html');

        static::assertTrue($type->isRegistered());
    }

    public function testIsRegisteredCustom(): void
    {
        $type = new MediaType('application', 'x-totally-custom-unknown');

        static::assertFalse($type->isRegistered());
    }

    public function testFromExtensionCss(): void
    {
        $type = MediaType::fromExtension('css');

        static::assertNotNull($type);
        static::assertSame('text/css', $type->essence());
    }

    public function testFromExtensionPdf(): void
    {
        $type = MediaType::fromExtension('pdf');

        static::assertNotNull($type);
        static::assertSame('application/pdf', $type->essence());
    }

    public function testAllRegisteredTypesAreValidMediaTypes(): void
    {
        foreach (Types::REGISTERED as $essence => $_) {
            $parts = Byte\split($essence, '/', 2);
            static::assertCount(2, $parts, 'Invalid essence format: ' . $essence);
            static::assertNotSame('', $parts[0], 'Empty type in: ' . $essence);
            static::assertNotSame('', $parts[1], 'Empty subtype in: ' . $essence);

            $type = MediaType::parse($essence);
            static::assertSame($essence, $type->essence(), 'Round-trip failed for: ' . $essence);
        }
    }

    public function testAllExtensionToTypeMappingsAreValid(): void
    {
        foreach (Types::EXTENSION_TO_TYPE as $extension => $essence) {
            static::assertNotSame('', $extension, 'Empty extension found');
            static::assertNotSame('', $essence, 'Empty essence for extension: ' . $extension);

            $type = MediaType::parse($essence);
            static::assertSame(
                $essence,
                $type->essence(),
                'Invalid essence for extension ' . $extension . ': ' . $essence,
            );
        }
    }

    public function testAllTypeToExtensionKeysAreValid(): void
    {
        foreach (Types::TYPE_TO_EXTENSIONS as $essence => $extensions) {
            $type = MediaType::parse($essence);
            static::assertSame($essence, $type->essence(), 'Invalid essence: ' . $essence);
            static::assertNotEmpty($extensions, 'Empty extensions for: ' . $essence);
        }
    }

    public function testFromExtensionEmpty(): void
    {
        static::assertNull(MediaType::fromExtension(''));
    }

    public function testExtensionsForCss(): void
    {
        $type = new MediaType('text', 'css');
        $extensions = $type->extensions();

        static::assertContains('css', $extensions);
    }

    public function testIsRegisteredPng(): void
    {
        $type = new MediaType('image', 'png');

        static::assertTrue($type->isRegistered());
    }

    public function testIsRegisteredOctetStream(): void
    {
        $type = new MediaType('application', 'octet-stream');

        static::assertTrue($type->isRegistered());
    }
}
