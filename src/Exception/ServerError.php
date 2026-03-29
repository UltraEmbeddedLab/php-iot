<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Exception;

/**
 * Thrown for server-side errors (busy, shutting down, unavailable).
 * Reason codes: 0x89 (Server busy), 0x8B (Server shutting down), etc.
 */
class ServerError extends MqttException
{
}
