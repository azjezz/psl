<?php

declare(strict_types=1);

namespace Psl\Tests\Xml\Issue;

use PHPUnit\Framework\TestCase;
use Psl\Xml\Issue\Issue;
use Psl\Xml\Issue\IssueCollection;
use Psl\Xml\Issue\Level;

class IssueCollectionTest extends TestCase
{
    use UseIssueTrait;

    public function testItActsAsAnIterator(): void
    {
        $issues = new IssueCollection(
            $issue1 = $this->createIssue(Level::warning()),
            $issue2 = $this->createIssue(Level::fatal()),
        );

        static::assertCount(2, $issues);
        static::assertSame([$issue1, $issue2], [...$issues]);
    }

    public function testItCanConvertToString(): void
    {
        $issues = new IssueCollection(
            $issue1 = $this->createIssue(Level::warning()),
            $issue2 = $this->createIssue(Level::fatal()),
        );

        $expected = implode(PHP_EOL, [
            '[WARNING] file.xml: message (1) on line 99,10',
            '[FATAL] file.xml: message (1) on line 99,10',
        ]);

        static::assertSame($expected, $issues->toString());
    }

    public function testItCanFilter(): void
    {
        $issues = new IssueCollection(
            $issue1 = $this->createIssue(Level::warning()),
            $issue2 = $this->createIssue(Level::fatal()),
        );
        $filtered = $issues->filter(static fn (Issue $issue): bool => !$issue->level()->isWarning());

        static::assertNotSame($issues, $filtered);
        static::assertCount(1, $filtered);
        static::assertSame([$issue2], [...$filtered]);
    }

    public function testItCanDetectTheHighestLevel(): void
    {
        $issues = new IssueCollection(
            $issue1 = $this->createIssue(Level::warning()),
            $issue2 = $this->createIssue(Level::fatal()),
        );

        static::assertTrue($issues->getHighestLevel()->isFatal());
    }

    public function testItCanDetectTheHighestLevelOnAnEmptyCollection(): void
    {
        $issues = new IssueCollection();

        static::assertNull($issues->getHighestLevel());
    }
}
