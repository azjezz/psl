<?php

declare(strict_types=1);

namespace Psl\Tests\Xml\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Tests\Xml\Issue\UseIssueTrait;
use Psl\Xml\Exception\ExceptionInterface;
use Psl\Xml\Exception\RuntimeException;
use Psl\Xml\Issue\IssueCollection;
use Psl\Xml\Issue\Level;

final class RuntimeExceptionTest extends TestCase
{
    use UseIssueTrait;

    public function testItCanThrowAnExceptionContainingXmlErrors(): void
    {
        $exception = new Exception('nonono');
        $issues = new IssueCollection(
            $this->createIssue(Level::fatal()),
            $this->createIssue(Level::warning()),
        );

        $this->expectException(RuntimeException::class);
        $this->expectException(ExceptionInterface::class);
        $this->expectExceptionMessage($exception->getMessage() . PHP_EOL . $issues->toString());

        throw RuntimeException::combineExceptionWithIssues($exception, $issues);
    }
}
