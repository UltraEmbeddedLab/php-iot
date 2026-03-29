<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Exception;

/**
 * Thrown when the broker rejects authentication (reason codes 0x86, 0x87).
 */
class AuthenticationError extends ProtocolError
{
}
