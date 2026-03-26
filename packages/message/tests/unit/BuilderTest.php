<?php

declare(strict_types=1);

namespace Psl\Message\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\DateTime;
use Psl\IO;
use Psl\Message;
use Psl\Message\Address\AddressList;
use Psl\Message\Address\Mailbox;
use Psl\Message\MessageId;
use Psl\MIME\Headers;
use Psl\MIME\Part;

use function array_map;

final class BuilderTest extends TestCase
{
    #[Test]
    public function buildTextOnly(): void
    {
        $message = new Message\Message()
            ->withFrom(AddressList::parse('alice@example.com'))
            ->withTo(AddressList::parse('bob@example.com'))
            ->withSubject('Hello')
            ->withContent(new Part\Text(new IO\MemoryHandle('Plain text body')));

        self::assertSame('Hello', $message->subject);
        self::assertNotNull($message->from);
        self::assertSame('alice@example.com', $message->from->mailboxes()[0]->address);
        self::assertSame('text/plain', $message->content->mediaType->essence());
    }

    #[Test]
    public function buildHtmlOnly(): void
    {
        $message = new Message\Message()->withContent(new Part\Text(new IO\MemoryHandle('<p>HTML body</p>'), 'html'));

        self::assertSame('text/html', $message->content->mediaType->essence());
    }

    #[Test]
    public function buildWithAllHeaders(): void
    {
        $date = DateTime\DateTime::parse('Mon, 01 Jan 2024 12:00:00 +0000', DateTime\FormatPattern::Rfc2822);

        $message = new Message\Message()
            ->withFrom(AddressList::parse('alice@example.com'))
            ->withSender(Mailbox::parse('sender@example.com'))
            ->withTo(AddressList::parse('bob@example.com'))
            ->withCc(AddressList::parse('cc@example.com'))
            ->withBcc(AddressList::parse('bcc@example.com'))
            ->withReplyTo(AddressList::parse('reply@example.com'))
            ->withDate($date)
            ->withMessageId(new MessageId('msg-001@example.com'))
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('body')));

        self::assertSame('alice@example.com', $message->headers->get('From'));
        self::assertSame('sender@example.com', $message->headers->get('Sender'));
        self::assertSame('bob@example.com', $message->headers->get('To'));
        self::assertSame('cc@example.com', $message->headers->get('Cc'));
        self::assertSame('bcc@example.com', $message->headers->get('Bcc'));
        self::assertSame('reply@example.com', $message->headers->get('Reply-To'));
        self::assertNotNull($message->headers->get('Date'));
        self::assertSame('<msg-001@example.com>', $message->headers->get('Message-ID'));
        self::assertSame('Test', $message->headers->get('Subject'));
    }

    #[Test]
    public function buildWithExtraHeaders(): void
    {
        $message = new Message\Message()
            ->withContent(new Part\Text(new IO\MemoryHandle('body')))
            ->withHeader('X-Custom', 'value1')
            ->withHeader('X-Another', 'value2');

        self::assertSame('value1', $message->headers->get('X-Custom'));
        self::assertSame('value2', $message->headers->get('X-Another'));
    }

    #[Test]
    public function buildWithGeneratedMessageId(): void
    {
        $message = new Message\Message()
            ->withContent(new Part\Text(new IO\MemoryHandle('body')))
            ->withGeneratedMessageId();

        self::assertNotNull($message->messageId);
        self::assertStringContainsString('@php-standard-library.dev', $message->messageId->id);
    }

    #[Test]
    public function immutability(): void
    {
        $body = new Part\Text(new IO\MemoryHandle('text'));

        $message1 = new Message\Message();
        $message2 = $message1->withContent($body);
        $message3 = $message2->withSubject('subject');

        self::assertNull($message1->subject);
        self::assertNull($message2->subject);
        self::assertSame('subject', $message3->subject);
        self::assertNotSame($message1, $message2);
        self::assertNotSame($message2, $message3);
    }

    #[Test]
    public function buildWithSubjectEncoding(): void
    {
        $message = new Message\Message()
            ->withSubject("J\u{00F6}hn")
            ->withContent(new Part\Text(new IO\MemoryHandle('body')));

        $rawSubject = $message->headers->get('Subject') ?? '';
        self::assertStringContainsString('=?', $rawSubject);

        self::assertSame("J\u{00F6}hn", $message->subject);
    }

    #[Test]
    public function roundTrip(): void
    {
        $message = new Message\Message()
            ->withFrom(AddressList::parse('alice@example.com'))
            ->withTo(AddressList::parse('bob@example.com'))
            ->withSubject('Round trip')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $serialized = Message\serialize($message)->readAll();
        $reparsed = Message\parse($serialized);

        self::assertSame('Round trip', $reparsed->subject);
        self::assertNotNull($reparsed->from);
        self::assertSame('alice@example.com', $reparsed->from->mailboxes()[0]->address);
        self::assertNotNull($reparsed->to);
        self::assertSame('bob@example.com', $reparsed->to->mailboxes()[0]->address);
    }

    #[Test]
    public function withReferences(): void
    {
        $ref1 = new MessageId('ref1@example.com');
        $ref2 = new MessageId('ref2@example.com');

        $message = new Message\Message()
            ->withContent(new Part\Text(new IO\MemoryHandle('body')))
            ->withReferences([$ref1, $ref2]);

        self::assertCount(2, $message->references);
        self::assertSame('ref1@example.com', $message->references[0]->id);
        self::assertSame('ref2@example.com', $message->references[1]->id);

        $rawReferences = $message->headers->get('References');
        self::assertSame('<ref1@example.com> <ref2@example.com>', $rawReferences);
    }

    #[Test]
    public function withInReplyTo(): void
    {
        $parent = new MessageId('parent@example.com');

        $message = new Message\Message()
            ->withContent(new Part\Text(new IO\MemoryHandle('body')))
            ->withInReplyTo([$parent]);

        self::assertCount(1, $message->inReplyTo);
        self::assertSame('parent@example.com', $message->inReplyTo[0]->id);

        $rawInReplyTo = $message->headers->get('In-Reply-To');
        self::assertSame('<parent@example.com>', $rawInReplyTo);
    }

    #[Test]
    public function threadingRoundTrip(): void
    {
        $ref1 = new MessageId('thread-root@example.com');
        $ref2 = new MessageId('reply-1@example.com');
        $parent = new MessageId('reply-1@example.com');

        $message = new Message\Message()
            ->withFrom(AddressList::parse('alice@example.com'))
            ->withTo(AddressList::parse('bob@example.com'))
            ->withSubject('Re: Thread')
            ->withContent(new Part\Text(new IO\MemoryHandle('My reply')))
            ->withReferences([$ref1, $ref2])
            ->withInReplyTo([$parent]);

        $serialized = Message\serialize($message)->readAll();
        $reparsed = Message\parse($serialized);

        self::assertCount(2, $reparsed->references);
        self::assertSame('thread-root@example.com', $reparsed->references[0]->id);
        self::assertSame('reply-1@example.com', $reparsed->references[1]->id);
        self::assertCount(1, $reparsed->inReplyTo);
        self::assertSame('reply-1@example.com', $reparsed->inReplyTo[0]->id);
    }

    #[Test]
    public function emptyReferencesNotEmitted(): void
    {
        $message = new Message\Message()
            ->withContent(new Part\Text(new IO\MemoryHandle('body')))
            ->withReferences([])
            ->withInReplyTo([]);

        self::assertNull($message->headers->get('References'));
        self::assertNull($message->headers->get('In-Reply-To'));
        self::assertSame([], $message->references);
        self::assertSame([], $message->inReplyTo);
    }

    #[Test]
    public function referencesImmutability(): void
    {
        $ref = new MessageId('ref@example.com');
        $builder1 = new Message\Message()->withContent(new Part\Text(new IO\MemoryHandle('body')));
        $builder2 = $builder1->withReferences([$ref]);

        self::assertSame([], $builder1->references);
        self::assertCount(1, $builder2->references);
    }

    #[Test]
    public function inReplyToImmutability(): void
    {
        $id = new MessageId('parent@example.com');
        $builder1 = new Message\Message()->withContent(new Part\Text(new IO\MemoryHandle('body')));
        $builder2 = $builder1->withInReplyTo([$id]);

        self::assertSame([], $builder1->inReplyTo);
        self::assertCount(1, $builder2->inReplyTo);
    }

    #[Test]
    public function defaultBuilderHasEmptyThreading(): void
    {
        $builder = new Message\Message();

        self::assertSame([], $builder->references);
        self::assertSame([], $builder->inReplyTo);
    }

    #[Test]
    public function buildWithGeneratedMessageIdCustomDomain(): void
    {
        $message = new Message\Message()
            ->withContent(new Part\Text(new IO\MemoryHandle('body')))
            ->withGeneratedMessageId('example.com');

        self::assertNotNull($message->messageId);
        self::assertStringContainsString('@example.com', $message->messageId->id);
    }

    #[Test]
    public function replyBasic(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',       'alice@example.com'],
            ['To',         'bob@example.com'],
            ['Subject',    'Hello'],
            ['Message-ID', '<msg-001@example.com>'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::reply($original, $me);

        self::assertNotNull($builder->from);
        self::assertSame('bob@example.com', $builder->from->mailboxes()[0]->address);
        self::assertNotNull($builder->to);
        self::assertSame('alice@example.com', $builder->to->mailboxes()[0]->address);
        self::assertSame('Re: Hello', $builder->subject);
        self::assertCount(1, $builder->inReplyTo);
        self::assertSame('msg-001@example.com', $builder->inReplyTo[0]->id);
        self::assertCount(1, $builder->references);
        self::assertSame('msg-001@example.com', $builder->references[0]->id);
    }

    #[Test]
    public function replyUsesReplyToWhenPresent(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',     'alice@example.com'],
            ['Reply-To', 'reply@example.com'],
            ['Subject',  'Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::reply($original, $me);

        self::assertNotNull($builder->to);
        self::assertSame('reply@example.com', $builder->to->mailboxes()[0]->address);
    }

    #[Test]
    public function replySkipsRePrefixIfAlreadyPresent(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['Subject', 'Re: Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::reply($original, $me);

        self::assertSame('Re: Hello', $builder->subject);
    }

    #[Test]
    public function replyPreservesReferenceChain(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',       'alice@example.com'],
            ['Message-ID', '<msg-002@example.com>'],
            ['References', '<msg-001@example.com>'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::reply($original, $me);

        self::assertCount(2, $builder->references);
        self::assertSame('msg-001@example.com', $builder->references[0]->id);
        self::assertSame('msg-002@example.com', $builder->references[1]->id);
    }

    #[Test]
    public function replyWithNoMessageId(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['Subject', 'Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::reply($original, $me);

        self::assertSame([], $builder->inReplyTo);
        self::assertSame([], $builder->references);
    }

    #[Test]
    public function replyWithNullSubject(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From', 'alice@example.com'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::reply($original, $me);

        self::assertNull($builder->subject);
    }

    #[Test]
    public function replyAllIncludesCc(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',       'alice@example.com'],
            ['To',         'bob@example.com, carol@example.com'],
            ['Cc',         'dave@example.com'],
            ['Subject',    'Discussion'],
            ['Message-ID', '<msg-001@example.com>'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::replyAll($original, $me);

        self::assertNotNull($builder->to);
        self::assertSame('alice@example.com', $builder->to->mailboxes()[0]->address);

        self::assertNotNull($builder->cc);
        $ccAddresses = $builder->cc->mailboxes();
        $ccEmails = array_map(static fn(Mailbox $m): string => $m->address, $ccAddresses);
        self::assertContains('carol@example.com', $ccEmails);
        self::assertContains('dave@example.com', $ccEmails);
        self::assertNotContains('bob@example.com', $ccEmails);
    }

    #[Test]
    public function replyAllExcludesSenderFromCc(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['To',      'bob@example.com'],
            ['Subject', 'Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::replyAll($original, $me);

        self::assertNull($builder->cc);
    }

    #[Test]
    public function forwardBasic(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',       'alice@example.com'],
            ['Subject',    'Hello'],
            ['Message-ID', '<msg-001@example.com>'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::forward($original, $me);

        self::assertNotNull($builder->from);
        self::assertSame('bob@example.com', $builder->from->mailboxes()[0]->address);
        self::assertNull($builder->to);
        self::assertSame('Fwd: Hello', $builder->subject);
        self::assertCount(1, $builder->inReplyTo);
        self::assertCount(1, $builder->references);
    }

    #[Test]
    public function forwardSkipsFwdPrefixIfAlreadyPresent(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['Subject', 'Fwd: Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $builder = Message\Message::forward($original, $me);

        self::assertSame('Fwd: Hello', $builder->subject);
    }

    #[Test]
    public function replyBuildAndSerializeRoundTrip(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',       'alice@example.com'],
            ['To',         'bob@example.com'],
            ['Subject',    'Hello'],
            ['Message-ID', '<msg-001@example.com>'],
        ]), new Part\Text(new IO\MemoryHandle('Original')));

        $me = new Mailbox('bob', 'example.com');
        $reply = Message\Message::reply($original, $me)->withContent(new Part\Text(new IO\MemoryHandle('Thanks!')));

        self::assertSame('Re: Hello', $reply->subject);
        self::assertCount(1, $reply->inReplyTo);
        self::assertCount(1, $reply->references);

        $serialized = Message\serialize($reply);
        $parsed = Message\parse($serialized->readAll());

        self::assertCount(1, $parsed->inReplyTo);
        self::assertCount(1, $parsed->references);
    }

    #[Test]
    public function withFromAcceptsString(): void
    {
        $message = new Message\Message()->withFrom('Alice <alice@example.com>');

        self::assertNotNull($message->from);
        self::assertSame('alice@example.com', $message->from->mailboxes()[0]->address);
        self::assertSame('Alice', $message->from->mailboxes()[0]->displayName);
    }

    #[Test]
    public function withFromAcceptsMailbox(): void
    {
        $message = new Message\Message()->withFrom(new Mailbox('bob', 'example.com'));

        self::assertNotNull($message->from);
        self::assertSame('bob@example.com', $message->from->mailboxes()[0]->address);
    }

    #[Test]
    public function withToAcceptsStringWithMultipleAddresses(): void
    {
        $message = new Message\Message()->withTo('alice@example.com, bob@example.com');

        self::assertNotNull($message->to);
        self::assertCount(2, $message->to->mailboxes());
        self::assertSame('alice@example.com', $message->to->mailboxes()[0]->address);
        self::assertSame('bob@example.com', $message->to->mailboxes()[1]->address);
    }

    #[Test]
    public function withCcAcceptsString(): void
    {
        $message = new Message\Message()->withCc('carol@example.com');

        self::assertNotNull($message->cc);
        self::assertSame('carol@example.com', $message->cc->mailboxes()[0]->address);
    }

    #[Test]
    public function withBccAcceptsMailbox(): void
    {
        $message = new Message\Message()->withBcc(new Mailbox('secret', 'example.com'));

        self::assertNotNull($message->bcc);
        self::assertSame('secret@example.com', $message->bcc->mailboxes()[0]->address);
    }

    #[Test]
    public function withReplyToAcceptsString(): void
    {
        $message = new Message\Message()->withReplyTo('support@example.com');

        self::assertNotNull($message->replyTo);
        self::assertSame('support@example.com', $message->replyTo->mailboxes()[0]->address);
    }

    #[Test]
    public function withSenderAcceptsString(): void
    {
        $message = new Message\Message()->withSender('noreply@example.com');

        self::assertNotNull($message->sender);
        self::assertSame('noreply@example.com', $message->sender->address);
    }

    #[Test]
    public function withSenderAcceptsMailbox(): void
    {
        $message = new Message\Message()->withSender(new Mailbox('admin', 'example.com'));

        self::assertNotNull($message->sender);
        self::assertSame('admin@example.com', $message->sender->address);
    }

    #[Test]
    public function mixedStringAndObjectAddresses(): void
    {
        $message = new Message\Message()
            ->withFrom('Alice <alice@example.com>')
            ->withTo(AddressList::parse('bob@example.com'))
            ->withCc(new Mailbox('carol', 'example.com'))
            ->withBcc('dave@example.com')
            ->withSender('noreply@example.com')
            ->withReplyTo('support@example.com')
            ->withContent(new Part\Text(new IO\MemoryHandle('body')));

        self::assertSame('alice@example.com', $message->from->mailboxes()[0]->address);
        self::assertSame('bob@example.com', $message->to->mailboxes()[0]->address);
        self::assertSame('carol@example.com', $message->cc->mailboxes()[0]->address);
        self::assertSame('dave@example.com', $message->bcc->mailboxes()[0]->address);
        self::assertSame('noreply@example.com', $message->sender->address);
        self::assertSame('support@example.com', $message->replyTo->mailboxes()[0]->address);
    }

    #[Test]
    public function stringAddressHeadersSyncCorrectly(): void
    {
        $message = new Message\Message()
            ->withFrom('alice@example.com')
            ->withTo('bob@example.com');

        self::assertSame('alice@example.com', $message->headers->get('From'));
        self::assertSame('bob@example.com', $message->headers->get('To'));
    }

    #[Test]
    public function defaultConstructorCreatesEmptyMessage(): void
    {
        $message = new Message\Message();

        self::assertNull($message->from);
        self::assertNull($message->to);
        self::assertNull($message->cc);
        self::assertNull($message->bcc);
        self::assertNull($message->sender);
        self::assertNull($message->replyTo);
        self::assertNull($message->date);
        self::assertNull($message->messageId);
        self::assertNull($message->subject);
        self::assertSame([], $message->references);
        self::assertSame([], $message->inReplyTo);
        self::assertSame('text/plain', $message->content->mediaType->essence());
    }

    #[Test]
    public function replyFallsBackToFromMailboxWhenNoFromOrReplyTo(): void
    {
        $original = new Message\Message(Headers::fromPairs([]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $reply = Message\Message::reply($original, $me);

        self::assertNotNull($reply->to);
        self::assertSame('bob@example.com', $reply->to->mailboxes()[0]->address);
    }

    #[Test]
    public function replyAllFallsBackToFromMailboxWhenNoFromOrReplyTo(): void
    {
        $original = new Message\Message(Headers::fromPairs([]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $reply = Message\Message::replyAll($original, $me);

        self::assertNotNull($reply->to);
        self::assertSame('bob@example.com', $reply->to->mailboxes()[0]->address);
        self::assertNull($reply->cc);
    }

    #[Test]
    public function replyAllWithOnlyToNoCc(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['To',      'bob@example.com, carol@example.com'],
            ['Subject', 'Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $reply = Message\Message::replyAll($original, $me);

        self::assertNotNull($reply->cc);
        $ccAddresses = array_map(static fn(Mailbox $m): string => $m->address, $reply->cc->mailboxes());

        self::assertContains('carol@example.com', $ccAddresses);
        self::assertNotContains('bob@example.com', $ccAddresses);
    }

    #[Test]
    public function forwardWithNoMessageId(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['Subject', 'Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $fwd = Message\Message::forward($original, $me);

        self::assertSame('Fwd: Hello', $fwd->subject);
        self::assertSame([], $fwd->inReplyTo);
        self::assertSame([], $fwd->references);
    }

    #[Test]
    public function forwardWithNullSubject(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From', 'alice@example.com'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $fwd = Message\Message::forward($original, $me);

        self::assertNull($fwd->subject);
    }

    #[Test]
    public function prefixSubjectCaseInsensitive(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['Subject', 'RE: Hello'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $reply = Message\Message::reply($original, $me);

        self::assertSame('RE: Hello', $reply->subject);
    }

    #[Test]
    public function withFromInvalidStringThrows(): void
    {
        $this->expectException(Message\Exception\ParsingException::class);

        new Message\Message()->withFrom('not valid <');
    }

    #[Test]
    public function withToInvalidStringThrows(): void
    {
        $this->expectException(Message\Exception\ParsingException::class);

        new Message\Message()->withTo('@invalid');
    }

    #[Test]
    public function withSenderInvalidStringThrows(): void
    {
        $this->expectException(Message\Exception\ParsingException::class);

        new Message\Message()->withSender('@');
    }

    #[Test]
    public function withCcInvalidStringThrows(): void
    {
        $this->expectException(Message\Exception\ParsingException::class);

        new Message\Message()->withCc('not<valid');
    }

    #[Test]
    public function withBccInvalidStringThrows(): void
    {
        $this->expectException(Message\Exception\ParsingException::class);

        new Message\Message()->withBcc('@missing-local');
    }

    #[Test]
    public function withReplyToInvalidStringThrows(): void
    {
        $this->expectException(Message\Exception\ParsingException::class);

        new Message\Message()->withReplyTo('bad<address');
    }

    #[Test]
    public function malformedMessageIdInReferencesIsSkipped(): void
    {
        $message = new Message\Message(Headers::fromPairs([
            ['From',       'alice@example.com'],
            ['References', '<valid@example.com> <> <also-valid@example.com>'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        self::assertCount(2, $message->references);
        self::assertSame('valid@example.com', $message->references[0]->id);
        self::assertSame('also-valid@example.com', $message->references[1]->id);
    }

    #[Test]
    public function withBodyReplacesContent(): void
    {
        $message = new Message\Message()->withContent(new Part\Text(new IO\MemoryHandle('first')));

        self::assertSame('text/plain', $message->content->mediaType->essence());

        $message = $message->withContent(new Part\Text(new IO\MemoryHandle('<h1>Hi</h1>'), 'html'));

        self::assertSame('text/html', $message->content->mediaType->essence());
    }

    #[Test]
    public function replyAllPrefersReplyToOverFrom(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',     'alice@example.com'],
            ['Reply-To', 'reply-list@example.com'],
            ['To',       'bob@example.com'],
            ['Subject',  'Discussion'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $reply = Message\Message::replyAll($original, $me);

        static::assertNotNull($reply->to);
        static::assertSame('reply-list@example.com', $reply->to->mailboxes()[0]->address);
    }

    #[Test]
    public function withFromIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withFrom('alice@example.com');

        static::assertNull($original->from);
        static::assertNotNull($modified->from);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withSenderIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withSender('admin@example.com');

        static::assertNull($original->sender);
        static::assertNotNull($modified->sender);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withToIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withTo('bob@example.com');

        static::assertNull($original->to);
        static::assertNotNull($modified->to);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withCcIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withCc('cc@example.com');

        static::assertNull($original->cc);
        static::assertNotNull($modified->cc);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withBccIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withBcc('bcc@example.com');

        static::assertNull($original->bcc);
        static::assertNotNull($modified->bcc);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withReplyToIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withReplyTo('reply@example.com');

        static::assertNull($original->replyTo);
        static::assertNotNull($modified->replyTo);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withDateIsImmutable(): void
    {
        $date = DateTime\DateTime::parse('Mon, 01 Jan 2024 12:00:00 +0000', DateTime\FormatPattern::Rfc2822);
        $original = new Message\Message();
        $modified = $original->withDate($date);

        static::assertNull($original->date);
        static::assertNotNull($modified->date);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withMessageIdIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withMessageId(new MessageId('test@example.com'));

        static::assertNull($original->messageId);
        static::assertNotNull($modified->messageId);
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withHeaderIsImmutable(): void
    {
        $original = new Message\Message();
        $modified = $original->withHeader('X-Test', 'value');

        static::assertNull($original->headers->get('X-Test'));
        static::assertSame('value', $modified->headers->get('X-Test'));
        static::assertNotSame($original, $modified);
    }

    #[Test]
    public function withReferencesSerializesIds(): void
    {
        $ref1 = new MessageId('ref1@example.com');
        $ref2 = new MessageId('ref2@example.com');

        $message = new Message\Message()->withReferences([$ref1, $ref2]);

        $rawReferences = $message->headers->get('References');
        static::assertSame('<ref1@example.com> <ref2@example.com>', $rawReferences);
    }

    #[Test]
    public function withInReplyToSerializesIds(): void
    {
        $parent = new MessageId('parent@example.com');

        $message = new Message\Message()->withInReplyTo([$parent]);

        $rawInReplyTo = $message->headers->get('In-Reply-To');
        static::assertSame('<parent@example.com>', $rawInReplyTo);
    }

    #[Test]
    public function prefixSubjectDoesNotMatchPartialPrefix(): void
    {
        $original = new Message\Message(Headers::fromPairs([
            ['From',    'alice@example.com'],
            ['Subject', 'React component discussion'],
        ]), new Part\Text(new IO\MemoryHandle('Hi')));

        $me = new Mailbox('bob', 'example.com');
        $reply = Message\Message::reply($original, $me);

        static::assertSame('Re: React component discussion', $reply->subject);
    }

    #[Test]
    public function parseDateWithWhitespaceTrimmed(): void
    {
        $message = new Message\Message(Headers::fromPairs([
            ['Date', '  Mon, 01 Jan 2024 12:00:00 +0000  '],
        ]));

        static::assertNotNull($message->date);
    }

    #[Test]
    public function parseReferencesWithNoAngleBracketsReturnsEmpty(): void
    {
        $message = new Message\Message(Headers::fromPairs([
            ['References', 'no-angle-brackets-here'],
        ]));

        static::assertSame([], $message->references);
    }
}
