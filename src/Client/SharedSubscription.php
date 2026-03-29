<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Client;

use InvalidArgumentException;

use function strlen;

/**
 * Helper for MQTT 5.0 Shared Subscriptions.
 *
 * Shared subscriptions allow multiple clients to share the load of messages
 * published to a topic. The broker distributes messages among subscribers
 * in a share group, so each message is delivered to only one subscriber
 * within the group.
 *
 * Topic filter format: $share/{ShareName}/{filter}
 *
 * Usage:
 * ```php
 * // Create a shared subscription filter
 * $filter = SharedSubscription::filter('mygroup', 'sensors/+/temp');
 * // Result: "$share/mygroup/sensors/+/temp"
 *
 * // Check if a filter is shared
 * SharedSubscription::isShared('$share/mygroup/sensors/+/temp'); // true
 * SharedSubscription::isShared('sensors/+/temp');                  // false
 *
 * // Parse a shared subscription filter
 * $parsed = SharedSubscription::parse('$share/mygroup/sensors/+/temp');
 * // Result: ['shareName' => 'mygroup', 'filter' => 'sensors/+/temp']
 * ```
 */
final class SharedSubscription
{
    private const string PREFIX = '$share/';

    /**
     * Build a shared subscription topic filter.
     *
     * @param string $shareName The share group name (must not be empty or contain '/')
     * @param string $topicFilter The underlying topic filter
     * @return string The shared subscription filter: $share/{shareName}/{filter}
     *
     * @throws InvalidArgumentException If shareName is empty or contains '/'
     */
    public static function filter(string $shareName, string $topicFilter): string
    {
        if ($shareName === '') {
            throw new InvalidArgumentException('Share name must not be empty');
        }

        if (str_contains($shareName, '/')) {
            throw new InvalidArgumentException('Share name must not contain "/"');
        }

        if ($topicFilter === '') {
            throw new InvalidArgumentException('Topic filter must not be empty');
        }

        return self::PREFIX . $shareName . '/' . $topicFilter;
    }

    /**
     * Check if a topic filter is a shared subscription.
     */
    public static function isShared(string $filter): bool
    {
        return str_starts_with($filter, self::PREFIX);
    }

    /**
     * Parse a shared subscription filter into its components.
     *
     * @return array{shareName: string, filter: string}|null Parsed components or null if not a shared subscription
     */
    public static function parse(string $filter): ?array
    {
        if (! self::isShared($filter)) {
            return null;
        }

        $rest     = substr($filter, strlen(self::PREFIX));
        $slashPos = strpos($rest, '/');

        if ($slashPos === false || $slashPos === 0) {
            return null;
        }

        $shareName   = substr($rest, 0, $slashPos);
        $topicFilter = substr($rest, $slashPos + 1);

        if ($topicFilter === '') {
            return null;
        }

        return ['shareName' => $shareName, 'filter' => $topicFilter];
    }
}
