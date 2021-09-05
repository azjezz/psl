<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use PhpBench\Attributes\ParamProviders;
use Psl\Type\TypeInterface;

abstract class GenericTypeBench
{
    /** @param array{type: TypeInterface, value: mixed} */
    #[ParamProviders('provideHappyPathCoercion')]
    final public function benchCoerce(array $input): mixed
    {
        return $input['type']->coerce($input['value']);
    }

    /** @return array<non-empty-string, array{type: TypeInterface, value: mixed}> */
    abstract public function provideHappyPathCoercion(): array;

    /** @param array{type: TypeInterface, value: mixed} */
    #[ParamProviders('provideHappyPathAssertion')]
    final public function benchAssert(array $input): void
    {
        $input['type']->assert($input['value']);
    }

    /** @return array<non-empty-string, array{type: TypeInterface, value: mixed}> */
    abstract public function provideHappyPathAssertion(): array;

    /** @param array{type: TypeInterface, value: mixed} */
    #[ParamProviders('provideHappyPathMatches')]
    final public function benchMatch(array $input): bool
    {
        return $input['type']->matches($input['value']);
    }

    /** @return array<non-empty-string, array{type: TypeInterface, value: mixed}> */
    abstract public function provideHappyPathMatches(): array;
}
