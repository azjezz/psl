<?php

declare(strict_types=1);

namespace Psl\Async\Exception;

use Exception;
use Exception as RootException;
use Psl\Str;

use function count;

use const PHP_EOL;

final class CompositeException extends Exception implements ExceptionInterface
{
    /**
     * @var non-empty-array<array-key, RootException>
     */
    private array $reasons;

    /**
     * @param non-empty-array<array-key, RootException> $reasons Array of exceptions.
     * @param string|null $message Exception message, defaults to message generated from passed exceptions.
     */
    public function __construct(array $reasons, ?string $message = null)
    {
        parent::__construct($message ?? $this->generateMessage($reasons));

        $this->reasons = $reasons;
    }

    /**
     * @return non-empty-array<array-key, RootException>
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    /**
     * @param non-empty-array<array-key, RootException> $reasons
     */
    private function generateMessage(array $reasons): string
    {
        $message = Str\format('"Multiple errors encountered (%d); use "%s::getReasons()" to retrieve the array of exceptions thrown:', count($reasons), self::class);

        foreach ($reasons as $reason) {
            $message .= PHP_EOL . PHP_EOL . $reason::class;

            if ($reason->getMessage() !== '') {
                $message .= ': ' . $reason->getMessage();
            }
        }

        return $message;
    }
}
