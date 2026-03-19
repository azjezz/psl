<?php

declare(strict_types=1);

namespace Psl\H2\Internal;

use Psl\H2\Setting;

use const Psl\H2\DEFAULT_HEADER_TABLE_SIZE;
use const Psl\H2\DEFAULT_INITIAL_WINDOW_SIZE;
use const Psl\H2\DEFAULT_MAX_CONCURRENT_STREAMS;
use const Psl\H2\DEFAULT_MAX_FRAME_SIZE;
use const Psl\H2\DEFAULT_MAX_HEADER_LIST_SIZE;

/**
 * Tracks local and remote HTTP/2 settings.
 *
 * @internal
 */
final class SettingsRegistry
{
    /**
     * @var array<positive-int, non-negative-int>
     */
    private array $local;

    /**
     * @var array<positive-int, non-negative-int>
     */
    private array $remote;

    private bool $localAcknowledged = false;

    /**
     * @param array<positive-int, non-negative-int> $localOverrides
     */
    public function __construct(array $localOverrides = [])
    {
        $defaults = [
            Setting::HeaderTableSize->value => DEFAULT_HEADER_TABLE_SIZE,
            Setting::EnablePush->value => 1,
            Setting::MaxConcurrentStreams->value => DEFAULT_MAX_CONCURRENT_STREAMS,
            Setting::InitialWindowSize->value => DEFAULT_INITIAL_WINDOW_SIZE,
            Setting::MaxFrameSize->value => DEFAULT_MAX_FRAME_SIZE,
            Setting::MaxHeaderListSize->value => DEFAULT_MAX_HEADER_LIST_SIZE,
        ];

        $this->local = $localOverrides + $defaults;
        $this->remote = $defaults;
    }

    /**
     * @return non-negative-int
     */
    public function localValue(Setting $setting): int
    {
        return $this->local[$setting->value];
    }

    /**
     * @return non-negative-int
     */
    public function remoteValue(Setting $setting): int
    {
        return $this->remote[$setting->value];
    }

    /**
     * @param array<positive-int, non-negative-int> $settings
     */
    public function applyRemote(array $settings): void
    {
        foreach ($settings as $id => $value) {
            $this->remote[$id] = $value;
        }
    }

    /**
     * Update local settings with new values.
     *
     * @param array<positive-int, non-negative-int> $settings
     */
    public function updateLocal(array $settings): void
    {
        foreach ($settings as $id => $value) {
            $this->local[$id] = $value;
        }

        $this->localAcknowledged = false;
    }

    /**
     * Mark local settings as acknowledged by the remote peer.
     */
    public function markLocalAcknowledged(): void
    {
        $this->localAcknowledged = true;
    }

    /**
     * Whether the remote peer has acknowledged our local settings.
     */
    public function isLocalAcknowledged(): bool
    {
        return $this->localAcknowledged;
    }

    /**
     * @return array<int, int>
     */
    public function localSettings(): array
    {
        return $this->local;
    }

    /**
     * Returns only non-default local settings for the initial SETTINGS frame.
     *
     * @return array<positive-int, non-negative-int>
     */
    public function localOverrides(): array
    {
        $defaults = [
            Setting::HeaderTableSize->value => DEFAULT_HEADER_TABLE_SIZE,
            Setting::EnablePush->value => 1,
            Setting::MaxConcurrentStreams->value => DEFAULT_MAX_CONCURRENT_STREAMS,
            Setting::InitialWindowSize->value => DEFAULT_INITIAL_WINDOW_SIZE,
            Setting::MaxFrameSize->value => DEFAULT_MAX_FRAME_SIZE,
            Setting::MaxHeaderListSize->value => DEFAULT_MAX_HEADER_LIST_SIZE,
        ];

        $overrides = [];
        foreach ($this->local as $id => $value) {
            if (!(!isset($defaults[$id]) || $defaults[$id] !== $value)) {
                continue;
            }

            $overrides[$id] = $value;
        }

        return $overrides;
    }
}
