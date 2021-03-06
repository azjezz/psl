<?php

declare(strict_types=1);

namespace Psl\Integration\Psalm;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        require_once __DIR__ . '/EventHandler/OptionalFunctionReturnTypeProvider.php';
        require_once __DIR__ . '/EventHandler/ShapeFunctionReturnTypeProvider.php';

        $registration->registerHooksFromClass(EventHandler\OptionalFunctionReturnTypeProvider::class);
        $registration->registerHooksFromClass(EventHandler\ShapeFunctionReturnTypeProvider::class);
    }
}
