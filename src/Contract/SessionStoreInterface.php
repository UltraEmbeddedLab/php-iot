<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Contract;

use ScienceStories\Mqtt\Session\SessionState;

/**
 * Interface for MQTT session persistence storage.
 *
 * MQTT sessions can be persisted to survive client restarts when using
 * Clean Session = false (MQTT 3.1.1) or Session Expiry Interval > 0 (MQTT 5.0).
 *
 * Implementations can store sessions in:
 * - Filesystem (FileSessionStore)
 * - Database (custom implementation)
 * - Redis/Memcached (custom implementation)
 * - Any other persistent storage
 *
 * Session data includes:
 * - Active subscriptions with QoS levels
 * - Pending QoS 2 message packet IDs
 * - Timestamp for cleanup purposes
 *
 * Usage:
 * ```php
 * // With FileSessionStore
 * $store = new FileSessionStore('/var/mqtt/sessions');
 * $options = (new Options('broker.local'))
 *     ->withSessionStore($store)
 *     ->withCleanSession(false);
 *
 * // Custom implementation
 * class RedisSessionStore implements SessionStoreInterface {
 *     public function save(string $clientId, SessionState $state): void {
 *         $this->redis->set("mqtt:session:$clientId", serialize($state));
 *     }
 *     // ...
 * }
 * ```
 */
interface SessionStoreInterface
{
    /**
     * Save session state for a client.
     *
     * @param string $clientId The MQTT client identifier
     * @param SessionState $state The session state to persist
     */
    public function save(string $clientId, SessionState $state): void;

    /**
     * Load session state for a client.
     *
     * @param string $clientId The MQTT client identifier
     * @return SessionState|null The restored state, or null if not found/expired
     */
    public function load(string $clientId): ?SessionState;

    /**
     * Delete session state for a client.
     *
     * @param string $clientId The MQTT client identifier
     */
    public function delete(string $clientId): void;

    /**
     * Check if a session exists for a client.
     *
     * @param string $clientId The MQTT client identifier
     */
    public function exists(string $clientId): bool;
}
