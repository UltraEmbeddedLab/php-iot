<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Session;

/**
 * Data transfer object representing MQTT session state for persistence.
 *
 * Stores the essential state required to resume an MQTT session:
 * - Subscriptions: Topic filters and their QoS levels
 * - Pending QoS 2: Packet IDs for incomplete QoS 2 exchanges
 * - Timestamp: When the session was last saved
 *
 * This state allows a client to resume a session after restart:
 * 1. Restore subscriptions automatically
 * 2. Complete pending QoS 2 message deliveries
 * 3. Determine if session has expired
 *
 * Usage:
 * ```php
 * // Create state from current client
 * $state = new SessionState(
 *     subscriptions: [
 *         'sensors/+/temp' => ['qos' => 1, 'options' => null],
 *         'alerts/#' => ['qos' => 2, 'options' => $opts],
 *     ],
 *     pendingQos2: [1234, 5678], // Packet IDs
 *     savedAt: time(),
 * );
 *
 * // Check if expired
 * if ($state->isExpired(3600)) { // 1 hour expiry
 *     $store->delete($clientId);
 * }
 * ```
 */
final class SessionState
{
    /**
     * @param array<string, array{qos: int, options: mixed}> $subscriptions Active subscriptions (filter => settings)
     * @param list<int> $pendingQos2 Packet IDs for pending QoS 2 messages
     * @param int $savedAt Unix timestamp when session was saved
     */
    public function __construct(
        public array $subscriptions = [],
        public array $pendingQos2 = [],
        public int $savedAt = 0,
    ) {
        if ($this->savedAt === 0) {
            $this->savedAt = time();
        }
    }

    /**
     * Check if the session has expired.
     *
     * @param int $expirySeconds Session expiry interval in seconds. 0 = never expires.
     */
    public function isExpired(int $expirySeconds): bool
    {
        if ($expirySeconds === 0) {
            return false;
        }

        return time() - $this->savedAt > $expirySeconds;
    }

    /**
     * Get the age of the session in seconds.
     */
    public function getAge(): int
    {
        return time() - $this->savedAt;
    }

    /**
     * Check if there are any active subscriptions.
     */
    public function hasSubscriptions(): bool
    {
        return count($this->subscriptions) > 0;
    }

    /**
     * Check if there are any pending QoS 2 messages.
     */
    public function hasPendingQos2(): bool
    {
        return count($this->pendingQos2) > 0;
    }

    /**
     * Get subscription count.
     */
    public function getSubscriptionCount(): int
    {
        return count($this->subscriptions);
    }

    /**
     * Serialize state to array for storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'subscriptions' => $this->subscriptions,
            'pending_qos2' => $this->pendingQos2,
            'saved_at' => $this->savedAt,
        ];
    }

    /**
     * Create state from array (deserialization).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, array{qos: int, options: mixed}> $subscriptions */
        $subscriptions = \is_array($data['subscriptions'] ?? null) ? $data['subscriptions'] : [];

        /** @var list<int> $pendingQos2 */
        $pendingQos2 = \is_array($data['pending_qos2'] ?? null) ? $data['pending_qos2'] : [];

        $savedAt = \is_int($data['saved_at'] ?? null) ? $data['saved_at'] : 0;

        return new self(
            subscriptions: $subscriptions,
            pendingQos2: $pendingQos2,
            savedAt: $savedAt,
        );
    }
}
