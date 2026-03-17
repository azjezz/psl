<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Fixture;

/**
 * @mago-expect lint:no-protected-in-final
 */
final class ClassWithMethods
{
    public function publicMethod(): void {}

    protected function protectedMethod(): void {}

    private function privateMethod(): void {}

    public static function publicStaticMethod(): void {}
}
