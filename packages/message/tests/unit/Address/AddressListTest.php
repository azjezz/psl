<?php

declare(strict_types=1);

namespace Psl\Message\Tests\Unit\Address;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Message\Address\AddressList;
use Psl\Message\Address\Group;
use Psl\Message\Address\Mailbox;

final class AddressListTest extends TestCase
{
    #[Test]
    public function constructEmpty(): void
    {
        $list = new AddressList([]);

        self::assertCount(0, $list);
        self::assertSame([], $list->addresses);
    }

    #[Test]
    public function ofSingleMailbox(): void
    {
        $mailbox = new Mailbox('user', 'example.com');
        $list = AddressList::of($mailbox);

        self::assertCount(1, $list);
        self::assertSame($mailbox, $list->addresses[0]);
    }

    #[Test]
    public function ofMultipleAddresses(): void
    {
        $a = new Mailbox('alice', 'example.com');
        $b = new Mailbox('bob', 'example.com');
        $list = AddressList::of($a, $b);

        self::assertCount(2, $list);
    }

    #[Test]
    public function parseSingleMailbox(): void
    {
        $list = AddressList::parse('user@example.com');

        self::assertCount(1, $list);
        self::assertInstanceOf(Mailbox::class, $list->addresses[0]);
        self::assertSame('user@example.com', $list->addresses[0]->address);
    }

    #[Test]
    public function parseMultipleMailboxes(): void
    {
        $list = AddressList::parse('alice@example.com, bob@example.com');

        self::assertCount(2, $list);
        self::assertInstanceOf(Mailbox::class, $list->addresses[0]);
        self::assertSame('alice@example.com', $list->addresses[0]->address);
        self::assertInstanceOf(Mailbox::class, $list->addresses[1]);
        self::assertSame('bob@example.com', $list->addresses[1]->address);
    }

    #[Test]
    public function parseWithDisplayNames(): void
    {
        $list = AddressList::parse('"Alice" <alice@example.com>, "Bob" <bob@example.com>');

        self::assertCount(2, $list);
        self::assertSame('Alice', $list->addresses[0]->displayName);
        self::assertSame('Bob', $list->addresses[1]->displayName);
    }

    #[Test]
    public function parseWithGroup(): void
    {
        $list = AddressList::parse('Team: alice@example.com, bob@example.com;');

        self::assertCount(1, $list);
        self::assertInstanceOf(Group::class, $list->addresses[0]);
        self::assertSame('Team', $list->addresses[0]->displayName);
        self::assertCount(2, $list->addresses[0]->mailboxes);
    }

    #[Test]
    public function parseMixedMailboxesAndGroups(): void
    {
        $list = AddressList::parse('admin@example.com, Team: alice@example.com;');

        self::assertCount(2, $list);
        self::assertInstanceOf(Mailbox::class, $list->addresses[0]);
        self::assertInstanceOf(Group::class, $list->addresses[1]);
    }

    #[Test]
    public function parseEmpty(): void
    {
        $list = AddressList::parse('');

        self::assertCount(0, $list);
    }

    #[Test]
    public function mailboxesFlattensGroups(): void
    {
        $alice = new Mailbox('alice', 'example.com');
        $bob = new Mailbox('bob', 'example.com');
        $admin = new Mailbox('admin', 'example.com');

        $list = new AddressList([
            $admin,
            new Group('Team', [$alice, $bob]),
        ]);

        $mailboxes = $list->mailboxes();
        self::assertCount(3, $mailboxes);
        self::assertSame('admin@example.com', $mailboxes[0]->address);
        self::assertSame('alice@example.com', $mailboxes[1]->address);
        self::assertSame('bob@example.com', $mailboxes[2]->address);
    }

    #[Test]
    public function iteratorAggregate(): void
    {
        $list = AddressList::of(new Mailbox('alice', 'example.com'), new Mailbox('bob', 'example.com'));

        $addresses = [];
        foreach ($list as $address) {
            $addresses[] = $address;
        }

        self::assertCount(2, $addresses);
    }

    #[Test]
    public function toStringCommaSeparated(): void
    {
        $list = AddressList::of(new Mailbox('alice', 'example.com'), new Mailbox('bob', 'example.com'));

        self::assertSame('alice@example.com, bob@example.com', $list->toString());
        self::assertSame('alice@example.com, bob@example.com', (string) $list);
    }

    #[Test]
    public function parseRoundTrip(): void
    {
        $original = AddressList::of(
            new Mailbox('alice', 'example.com', 'Alice'),
            new Mailbox('bob', 'example.com', 'Bob'),
        );

        $parsed = AddressList::parse($original->toString());

        self::assertCount(2, $parsed);
        self::assertInstanceOf(Mailbox::class, $parsed->addresses[0]);
        self::assertSame('alice@example.com', $parsed->addresses[0]->address);
        self::assertInstanceOf(Mailbox::class, $parsed->addresses[1]);
        self::assertSame('bob@example.com', $parsed->addresses[1]->address);
    }

    #[Test]
    public function parseWithCommaInQuotedDisplayName(): void
    {
        $list = AddressList::parse('"Doe, John" <john@example.com>, bob@example.com');

        self::assertCount(2, $list);
        static::assertInstanceOf(Mailbox::class, $list->addresses[0]);
        static::assertInstanceOf(Mailbox::class, $list->addresses[1]);
        self::assertSame('Doe, John', $list->addresses[0]->displayName);
        self::assertSame('bob@example.com', $list->addresses[1]->address);
    }

    #[Test]
    public function parseWithEscapedQuoteInDisplayName(): void
    {
        $list = AddressList::parse('"O\\"Brien" <john@example.com>');

        self::assertCount(1, $list);
        static::assertInstanceOf(Mailbox::class, $list->addresses[0]);
        self::assertSame('john@example.com', $list->addresses[0]->address);
    }

    #[Test]
    public function parseSkipsEmptySegments(): void
    {
        $list = AddressList::parse('alice@example.com, , bob@example.com');

        self::assertCount(2, $list);
    }

    #[Test]
    public function toStringEmpty(): void
    {
        $list = new AddressList([]);

        self::assertSame('', $list->toString());
    }

    #[Test]
    public function toStringWithGroup(): void
    {
        $group = new Group('Team', [
            new Mailbox('alice', 'example.com'),
        ]);
        $list = new AddressList([$group]);

        self::assertSame('Team: alice@example.com;', $list->toString());
    }

    #[Test]
    public function parseCommaOnlyInput(): void
    {
        $list = AddressList::parse(',,,');

        self::assertCount(0, $list);
    }

    #[Test]
    public function mailboxesFromEmptyList(): void
    {
        $list = new AddressList([]);

        self::assertSame([], $list->mailboxes());
    }

    #[Test]
    public function countReturnsAddressCount(): void
    {
        $list = AddressList::of(new Mailbox('a', 'x.com'), new Mailbox('b', 'x.com'), new Mailbox('c', 'x.com'));

        self::assertCount(3, $list);
    }

    #[Test]
    public function excludeRemovesMatchingMailbox(): void
    {
        $list = AddressList::of(
            new Mailbox('alice', 'example.com'),
            new Mailbox('bob', 'example.com'),
            new Mailbox('carol', 'example.com'),
        );

        $filtered = $list->exclude(new Mailbox('bob', 'example.com'));

        self::assertCount(2, $filtered);
        static::assertInstanceOf(Mailbox::class, $filtered->addresses[0]);
        static::assertInstanceOf(Mailbox::class, $filtered->addresses[1]);
        self::assertSame('alice@example.com', $filtered->addresses[0]->address);
        self::assertSame('carol@example.com', $filtered->addresses[1]->address);
    }

    #[Test]
    public function excludeIsCaseInsensitive(): void
    {
        $list = AddressList::of(new Mailbox('Bob', 'Example.COM'));

        $filtered = $list->exclude(new Mailbox('bob', 'example.com'));

        self::assertCount(0, $filtered);
    }

    #[Test]
    public function excludeFiltersInsideGroups(): void
    {
        $group = new Group('Team', [
            new Mailbox('alice', 'example.com'),
            new Mailbox('bob', 'example.com'),
        ]);
        $list = new AddressList([$group]);

        $filtered = $list->exclude(new Mailbox('bob', 'example.com'));

        self::assertCount(1, $filtered);
        self::assertInstanceOf(Group::class, $filtered->addresses[0]);
        self::assertCount(1, $filtered->addresses[0]->mailboxes);
        self::assertSame('alice@example.com', $filtered->addresses[0]->mailboxes[0]->address);
    }

    #[Test]
    public function excludeRemovesEmptyGroups(): void
    {
        $group = new Group('Solo', [new Mailbox('bob', 'example.com')]);
        $list = new AddressList([$group]);

        $filtered = $list->exclude(new Mailbox('bob', 'example.com'));

        self::assertCount(0, $filtered);
    }

    #[Test]
    public function excludeNoMatch(): void
    {
        $list = AddressList::of(new Mailbox('alice', 'example.com'));

        $filtered = $list->exclude(new Mailbox('bob', 'example.com'));

        self::assertCount(1, $filtered);
    }

    #[Test]
    public function mergeMultipleLists(): void
    {
        $a = AddressList::of(new Mailbox('alice', 'example.com'));
        $b = AddressList::of(new Mailbox('bob', 'example.com'));
        $c = AddressList::of(new Mailbox('carol', 'example.com'));

        $merged = AddressList::merge($a, $b, $c);

        self::assertCount(3, $merged);
        static::assertInstanceOf(Mailbox::class, $merged->addresses[0]);
        static::assertInstanceOf(Mailbox::class, $merged->addresses[1]);
        static::assertInstanceOf(Mailbox::class, $merged->addresses[2]);
        self::assertSame('alice@example.com', $merged->addresses[0]->address);
        self::assertSame('bob@example.com', $merged->addresses[1]->address);
        self::assertSame('carol@example.com', $merged->addresses[2]->address);
    }

    #[Test]
    public function mergeEmpty(): void
    {
        $merged = AddressList::merge();

        self::assertCount(0, $merged);
    }
}
