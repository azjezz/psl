<?php

declare(strict_types=1);

namespace Psl\Integration\Psalm\EventHandler;

use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;

final class OptionalFunctionReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    /**
     * @return array<lowercase-string>
     */
    public static function getFunctionIds(): array
    {
        return [
            'psl\type\optional'
        ];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $statements_source = $event->getStatementsSource();
        $call_args = $event->getCallArgs();

        $argument = $call_args[0] ?? null;
        if (null === $argument) {
            return null;
        }

        $argument_value = $argument->value;
        $type = $statements_source->getNodeTypeProvider()->getType($argument_value);
        if (null === $type) {
            return null;
        }

        $clone = clone $type;
        $clone->possibly_undefined = true;

        return $clone;
    }
}
