<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Client;

/**
 * Manages MQTT 5.0 Flow Control via Receive Maximum.
 *
 * Flow control limits the number of unacknowledged QoS 1/2 messages in flight
 * to prevent overwhelming slower receivers.
 *
 * MQTT 5.0 Receive Maximum:
 * - Client declares its receive_maximum in CONNECT properties
 * - Broker declares its receive_maximum in CONNACK properties
 * - Neither side may have more than receive_maximum unacknowledged QoS 1/2 packets
 * - Default value is 65,535 if not specified
 *
 * Usage:
 * ```php
 * $flow = new FlowControl(10); // max 10 in-flight
 *
 * if ($flow->canSend()) {
 *     $flow->trackSend($packetId);
 *     // ... send QoS 1/2 message
 * }
 *
 * // On PUBACK/PUBCOMP received:
 * $flow->trackAck($packetId);
 * ```
 */
final class FlowControl
{
    /** @var int Maximum concurrent in-flight messages */
    private int $maxInFlight;

    /** @var int Current count of in-flight messages */
    private int $currentInFlight = 0;

    /** @var array<int, float> Packet ID => timestamp of tracked messages */
    private array $pending = [];

    /**
     * @param int $maxInFlight Maximum concurrent in-flight QoS 1/2 messages.
     *                         Default 65535 per MQTT 5.0 spec.
     */
    public function __construct(int $maxInFlight = 65535)
    {
        $this->maxInFlight = max(1, min($maxInFlight, 65535));
    }

    /**
     * Check if a new QoS 1/2 message can be sent.
     */
    public function canSend(): bool
    {
        return $this->currentInFlight < $this->maxInFlight;
    }

    /**
     * Track a sent QoS 1/2 message.
     *
     * @param int $packetId The packet identifier
     */
    public function trackSend(int $packetId): void
    {
        if (! isset($this->pending[$packetId])) {
            $this->pending[$packetId] = microtime(true);
            $this->currentInFlight++;
        }
    }

    /**
     * Track acknowledgment of a QoS 1/2 message.
     *
     * @param int $packetId The packet identifier
     */
    public function trackAck(int $packetId): void
    {
        if (isset($this->pending[$packetId])) {
            unset($this->pending[$packetId]);
            $this->currentInFlight = max(0, $this->currentInFlight - 1);
        }
    }

    /**
     * Get current number of in-flight messages.
     */
    public function getInFlightCount(): int
    {
        return $this->currentInFlight;
    }

    /**
     * Get maximum allowed in-flight messages.
     */
    public function getMaxInFlight(): int
    {
        return $this->maxInFlight;
    }

    /**
     * Get the pending packet IDs.
     *
     * @return list<int>
     */
    public function getPendingPacketIds(): array
    {
        return array_keys($this->pending);
    }

    /**
     * Check if a specific packet ID is pending.
     */
    public function isPending(int $packetId): bool
    {
        return isset($this->pending[$packetId]);
    }

    /**
     * Get the timestamp when a packet was sent.
     *
     * @param int $packetId The packet identifier
     * @return float|null Unix timestamp with microseconds, or null if not tracked
     */
    public function getSendTime(int $packetId): ?float
    {
        return $this->pending[$packetId] ?? null;
    }

    /**
     * Get packets that have been pending longer than the specified timeout.
     *
     * @param float $timeoutSeconds Timeout in seconds
     * @return list<int> Packet IDs that have timed out
     */
    public function getTimedOutPackets(float $timeoutSeconds): array
    {
        $now      = microtime(true);
        $timedOut = [];

        foreach ($this->pending as $packetId => $sendTime) {
            if ($now - $sendTime > $timeoutSeconds) {
                $timedOut[] = $packetId;
            }
        }

        return $timedOut;
    }

    /**
     * Reset flow control state.
     * Should be called on disconnect/reconnect.
     */
    public function reset(): void
    {
        $this->pending         = [];
        $this->currentInFlight = 0;
    }

    /**
     * Update the maximum in-flight count.
     * Used when broker's receive_maximum is received in CONNACK.
     */
    public function setMaxInFlight(int $max): void
    {
        $this->maxInFlight = max(1, min($max, 65535));
    }

    /**
     * Wait until a slot is available.
     *
     * Note: In single-threaded PHP, this method is useful when combined with
     * external state updates (e.g., via loopOnce() in the Client). The caller
     * must ensure trackAck() is called to release slots.
     *
     * @param float $pollIntervalSec Polling interval in seconds
     * @param float|null $timeoutSec Maximum wait time, null for indefinite
     * @return bool True if slot became available, false on timeout
     */
    public function waitForSlot(float $pollIntervalSec = 0.01, ?float $timeoutSec = null): bool
    {
        if ($this->canSend()) {
            return true;
        }

        $deadline = $timeoutSec !== null ? microtime(true) + $timeoutSec : null;

        // @phpstan-ignore-next-line canSend() state changes via trackAck()
        while (! $this->canSend()) {
            if ($deadline !== null && microtime(true) >= $deadline) {
                return false;
            }
            usleep((int) ($pollIntervalSec * 1_000_000));
        }

        // @phpstan-ignore-next-line while loop exits when canSend() returns true
        return true;
    }
}
