<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Exception;

/**
 * Thrown when broker rate/quota limits are exceeded.
 * Reason codes: 0x93 (Receive Maximum exceeded), 0x96 (Message rate too high), 0x97 (Quota exceeded).
 */
class QuotaExceeded extends MqttException
{
}
