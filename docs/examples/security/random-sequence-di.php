<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Iter;
use Psl\RandomSequence\MersenneTwisterSequence;
use Psl\RandomSequence\SecureSequence;
use Psl\RandomSequence\SequenceInterface;

/**
 * Picks a random winner from the list of participants using the provided RNG.
 *
 * @param non-empty-list<non-empty-string> $participants
 *
 * @return non-empty-string The name of the winner.
 */
function pick_winner(array $participants, SequenceInterface $rng): string
{
    /** @var int<0, max> $index */
    $index = $rng->next() % Iter\count::<string>($participants);

    return $participants[$index];
}

// Deterministic for tests
$testRng = new MersenneTwisterSequence(seed: 42);
$winner = pick_winner(['Alice', 'Bob', 'Charlie'], $testRng);
IO\write_line('Test winner: %s', $winner);

// Secure for production
$prodRng = new SecureSequence();
$winner = pick_winner(['Alice', 'Bob', 'Charlie'], $prodRng);
IO\write_line('Production winner: %s', $winner);
