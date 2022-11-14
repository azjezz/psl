<?php

declare(strict_types=1);

namespace Psl\Internal\ForeignFunctionInterface;

use FFI;
use Psl\Class;
use Psl\File;

final class MathBinding {
    private const LIB = __DIR__ . "/../../../../libs/math/lib.so";
    private const HEADER = __DIR__ . "/../../../../libs/math/lib.h";

    private static ?bool $enabled = null;
    private static ?MathBinding $instance = null;

    private function __construct(
        private FFI $binding,
    ) {}

    public static function get(): ?MathBinding
    {
        if (static::$enabled === null) {
            static::$enabled = Class\exists(FFI::class);
        }

        if (!static::$enabled) {
            return null;
        }

        return static::$instance ??= new static(FFI::cdef(File\read(static::HEADER), static::LIB));
    }

    public function from_base(string $number, int $base): int
    {
        return $this->binding->from_base($number, $base);
    }

    public function to_base(int $number, int $base): string
    {
        return $this->binding->to_base($number, $base);
    }
}
