<?php

declare(strict_types=1);

namespace Psl\TCP\TLS\Exception;

use Psl\Network\Exception;

final class NegotiationException extends Exception\RuntimeException implements ExceptionInterface
{
}