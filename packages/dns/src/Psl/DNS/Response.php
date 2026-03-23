<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\DNS\Record\RecordInterface;

use function array_filter;
use function array_values;

/**
 * A decoded DNS response containing answer, authority, and additional record sections.
 *
 * @api
 *
 * @mago-expect lint:excessive-parameter-list
 */
final readonly class Response
{
    /**
     * @param int $id The transaction ID matching the original query.
     * @param ResponseCode $code The response status code.
     * @param list<RecordInterface> $answers Answer section records.
     * @param list<RecordInterface> $authority Authority section records.
     * @param list<RecordInterface> $additional Additional section records.
     * @param bool $authoritativeAnswer Whether the response came from an authoritative server.
     * @param bool $recursionDesired Whether recursion was requested in the query.
     * @param bool $recursionAvailable Whether the server supports recursive queries.
     * @param bool $authenticatedData Whether the response data was DNSSEC-validated (AD flag).
     * @param bool $checkingDisabled Whether DNSSEC checking was disabled (CD flag).
     */
    public function __construct(
        public int $id,
        public ResponseCode $code,
        public array $answers,
        public array $authority,
        public array $additional,
        public bool $authoritativeAnswer = false,
        public bool $recursionDesired = false,
        public bool $recursionAvailable = false,
        public bool $authenticatedData = false,
        public bool $checkingDisabled = false,
    ) {}

    /**
     * Filter the answer section for records of a specific type.
     *
     * @template T of RecordInterface
     *
     * @param class-string<T> $type The record class to filter by.
     *
     * @return list<T>
     */
    public function getAnswerRecords(string $type): array
    {
        /** @var list<T> */
        return array_values(array_filter($this->answers, static fn(RecordInterface $r): bool => $r instanceof $type));
    }

    /**
     * Return the first answer record matching the given type, or null if none found.
     *
     * @template T of RecordInterface
     *
     * @param class-string<T> $type The record class to search for.
     *
     * @return null|T
     */
    public function getFirstAnswerRecord(string $type): null|RecordInterface
    {
        foreach ($this->answers as $record) {
            if ($record instanceof $type) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Filter the authority section for records of a specific type.
     *
     * @template T of RecordInterface
     *
     * @param class-string<T> $type The record class to filter by.
     *
     * @return list<T>
     */
    public function getAuthorityRecords(string $type): array
    {
        /** @var list<T> */
        return array_values(array_filter($this->authority, static fn(RecordInterface $r): bool => $r instanceof $type));
    }

    /**
     * Filter the additional section for records of a specific type.
     *
     * @template T of RecordInterface
     *
     * @param class-string<T> $type The record class to filter by.
     *
     * @return list<T>
     */
    public function getAdditionalRecords(string $type): array
    {
        /** @var list<T> */
        return array_values(array_filter(
            $this->additional,
            static fn(RecordInterface $r): bool => $r instanceof $type,
        ));
    }
}
