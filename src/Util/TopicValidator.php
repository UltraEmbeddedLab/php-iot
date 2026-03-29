<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Util;

use ScienceStories\Mqtt\Exception\ProtocolError;

/**
 * Validates MQTT topic names and subscription filters per MQTT spec.
 *
 * MQTT Topic Rules:
 * - Must not be empty (for PUBLISH topics)
 * - Must not exceed 65535 bytes (UTF-8 encoded)
 * - Must not contain null character (U+0000)
 * - PUBLISH topics must not contain wildcard characters (+ or #)
 * - SUBSCRIBE filters: + must occupy entire level, # must be last character
 */
final class TopicValidator
{
    private const int MAX_TOPIC_LENGTH = 65535;

    /**
     * Validate a topic name for PUBLISH operations.
     *
     * @throws ProtocolError If the topic is invalid
     */
    public static function validatePublishTopic(string $topic): void
    {
        if ($topic === '') {
            throw new ProtocolError('PUBLISH topic must not be empty');
        }

        if (\strlen($topic) > self::MAX_TOPIC_LENGTH) {
            throw new ProtocolError('Topic exceeds maximum length of 65535 bytes');
        }

        if (str_contains($topic, "\0")) {
            throw new ProtocolError('Topic must not contain null character (U+0000)');
        }

        if (str_contains($topic, '+') || str_contains($topic, '#')) {
            throw new ProtocolError('PUBLISH topic must not contain wildcard characters (+ or #)');
        }
    }

    /**
     * Validate a topic filter for SUBSCRIBE operations.
     *
     * @throws ProtocolError If the filter is invalid
     */
    public static function validateSubscribeFilter(string $filter): void
    {
        if ($filter === '') {
            throw new ProtocolError('SUBSCRIBE filter must not be empty');
        }

        if (\strlen($filter) > self::MAX_TOPIC_LENGTH) {
            throw new ProtocolError('Topic filter exceeds maximum length of 65535 bytes');
        }

        if (str_contains($filter, "\0")) {
            throw new ProtocolError('Topic filter must not contain null character (U+0000)');
        }

        $levels    = explode('/', $filter);
        $lastIndex = \count($levels) - 1;

        foreach ($levels as $i => $level) {
            // Single-level wildcard (+) must occupy an entire level
            if (str_contains($level, '+') && $level !== '+') {
                throw new ProtocolError('Single-level wildcard (+) must occupy an entire topic level');
            }

            // Multi-level wildcard (#) must be last level and stand alone
            if (str_contains($level, '#')) {
                if ($level !== '#') {
                    throw new ProtocolError('Multi-level wildcard (#) must occupy an entire topic level');
                }
                if ($i !== $lastIndex) {
                    throw new ProtocolError('Multi-level wildcard (#) must be the last level in the filter');
                }
            }
        }
    }

    /**
     * Check if a topic name is valid for PUBLISH.
     */
    public static function isValidPublishTopic(string $topic): bool
    {
        try {
            self::validatePublishTopic($topic);

            return true;
        } catch (ProtocolError) {
            return false;
        }
    }

    /**
     * Check if a topic filter is valid for SUBSCRIBE.
     */
    public static function isValidSubscribeFilter(string $filter): bool
    {
        try {
            self::validateSubscribeFilter($filter);

            return true;
        } catch (ProtocolError) {
            return false;
        }
    }
}
