<?php

declare(strict_types=1);

namespace Psl\Message\Tests\Unit\Address;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Message\Address\Group;
use Psl\Message\Address\Mailbox;
use Psl\Message\Exception\ParsingException;

final class GroupTest extends TestCase
{
    #[Test]
    public function constructWithMailboxes(): void
    {
        $group = new Group('Team', [
            new Mailbox('alice', 'example.com', 'Alice'),
            new Mailbox('bob', 'example.com', 'Bob'),
        ]);

        self::assertSame('Team', $group->displayName);
        self::assertCount(2, $group->mailboxes);
    }

    #[Test]
    public function constructEmptyGroup(): void
    {
        $group = new Group('Undisclosed Recipients');

        self::assertSame('Undisclosed Recipients', $group->displayName);
        self::assertSame([], $group->mailboxes);
    }

    #[Test]
    public function parseWithMailboxes(): void
    {
        $group = Group::parse('Team: alice@example.com, bob@example.com;');

        self::assertSame('Team', $group->displayName);
        self::assertCount(2, $group->mailboxes);
        self::assertSame('alice@example.com', $group->mailboxes[0]->address);
        self::assertSame('bob@example.com', $group->mailboxes[1]->address);
    }

    #[Test]
    public function parseEmptyGroup(): void
    {
        $group = Group::parse('Undisclosed Recipients:;');

        self::assertSame('Undisclosed Recipients', $group->displayName);
        self::assertSame([], $group->mailboxes);
    }

    #[Test]
    public function parseMissingColonThrows(): void
    {
        $this->expectException(ParsingException::class);

        Group::parse('no colon here');
    }

    #[Test]
    public function parseMissingSemicolonThrows(): void
    {
        $this->expectException(ParsingException::class);

        Group::parse('Team: alice@example.com');
    }

    #[Test]
    public function toStringWithMailboxes(): void
    {
        $group = new Group('Team', [
            new Mailbox('alice', 'example.com'),
            new Mailbox('bob', 'example.com'),
        ]);

        self::assertSame('Team: alice@example.com, bob@example.com;', $group->toString());
        self::assertSame('Team: alice@example.com, bob@example.com;', (string) $group);
    }

    #[Test]
    public function toStringEmptyGroup(): void
    {
        $group = new Group('Undisclosed Recipients');

        self::assertSame('Undisclosed Recipients:;', $group->toString());
    }

    #[Test]
    public function parseRoundTrip(): void
    {
        $original = new Group('Team', [
            new Mailbox('alice', 'example.com'),
            new Mailbox('bob', 'example.com'),
        ]);

        $parsed = Group::parse($original->toString());

        self::assertSame($original->displayName, $parsed->displayName);
        self::assertCount(2, $parsed->mailboxes);
        self::assertSame('alice@example.com', $parsed->mailboxes[0]->address);
    }

    #[Test]
    public function parseSingleMember(): void
    {
        $group = Group::parse('Admin: admin@example.com;');

        self::assertSame('Admin', $group->displayName);
        self::assertCount(1, $group->mailboxes);
        self::assertSame('admin@example.com', $group->mailboxes[0]->address);
    }

    #[Test]
    public function parseTrimsWhitespace(): void
    {
        $group = Group::parse('  Team  :  alice@example.com  ;  ');

        self::assertSame('Team', $group->displayName);
        self::assertCount(1, $group->mailboxes);
    }

    #[Test]
    public function parseWithDisplayNameMailboxes(): void
    {
        $group = Group::parse('Team: "Alice" <alice@example.com>, Bob <bob@example.com>;');

        self::assertCount(2, $group->mailboxes);
        self::assertSame('Alice', $group->mailboxes[0]->displayName);
        self::assertSame('Bob', $group->mailboxes[1]->displayName);
    }

    #[Test]
    public function parseSkipsEmptyMailboxParts(): void
    {
        $group = Group::parse('Team: alice@example.com, , bob@example.com;');

        self::assertCount(2, $group->mailboxes);
    }

    #[Test]
    public function stringableInterface(): void
    {
        $group = new Group('Team', [new Mailbox('a', 'x.com')]);

        self::assertSame($group->toString(), (string) $group);
    }
}
