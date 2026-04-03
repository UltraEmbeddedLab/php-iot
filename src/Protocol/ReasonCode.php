<?php

declare(strict_types=1);

namespace ScienceStories\Mqtt\Protocol;

use ScienceStories\Mqtt\Exception\AuthenticationError;
use ScienceStories\Mqtt\Exception\MqttException;
use ScienceStories\Mqtt\Exception\ProtocolError;
use ScienceStories\Mqtt\Exception\QuotaExceeded;
use ScienceStories\Mqtt\Exception\ServerError;

use function array_key_exists;
use function dechex;

/**
 * MQTT 5.0 Reason Codes with descriptions and exception mapping.
 */
enum ReasonCode: int
{
    case Success                             = 0x00;
    case GrantedQoS1                         = 0x01;
    case GrantedQoS2                         = 0x02;
    case DisconnectWithWill                  = 0x04;
    case NoMatchingSubscribers               = 0x10;
    case NoSubscriptionExisted               = 0x11;
    case ContinueAuthentication              = 0x18;
    case ReAuthenticate                      = 0x19;
    case UnspecifiedError                    = 0x80;
    case MalformedPacket                     = 0x81;
    case ProtocolError                       = 0x82;
    case ImplementationSpecificError         = 0x83;
    case UnsupportedProtocolVersion          = 0x84;
    case ClientIdentifierNotValid            = 0x85;
    case BadUserNameOrPassword               = 0x86;
    case NotAuthorized                       = 0x87;
    case ServerUnavailable                   = 0x88;
    case ServerBusy                          = 0x89;
    case Banned                              = 0x8A;
    case ServerShuttingDown                  = 0x8B;
    case BadAuthenticationMethod             = 0x8C;
    case KeepAliveTimeout                    = 0x8D;
    case SessionTakenOver                    = 0x8E;
    case TopicFilterInvalid                  = 0x8F;
    case TopicNameInvalid                    = 0x90;
    case PacketIdentifierInUse               = 0x91;
    case PacketIdentifierNotFound            = 0x92;
    case ReceiveMaximumExceeded              = 0x93;
    case TopicAliasInvalid                   = 0x94;
    case PacketTooLarge                      = 0x95;
    case MessageRateTooHigh                  = 0x96;
    case QuotaExceeded                       = 0x97;
    case AdministrativeAction                = 0x98;
    case PayloadFormatInvalid                = 0x99;
    case RetainNotSupported                  = 0x9A;
    case QoSNotSupported                     = 0x9B;
    case UseAnotherServer                    = 0x9C;
    case ServerMoved                         = 0x9D;
    case SharedSubscriptionsNotSupported     = 0x9E;
    case ConnectionRateExceeded              = 0x9F;
    case MaximumConnectTime                  = 0xA0;
    case SubscriptionIdentifiersNotSupported = 0xA1;
    case WildcardSubscriptionsNotSupported   = 0xA2;

    /** @var array<int, string> */
    private const array DESCRIPTIONS = [
        0x00 => 'Success',
        0x01 => 'Granted QoS 1',
        0x02 => 'Granted QoS 2',
        0x04 => 'Disconnect with Will Message',
        0x10 => 'No matching subscribers',
        0x11 => 'No subscription existed',
        0x18 => 'Continue authentication',
        0x19 => 'Re-authenticate',
        0x80 => 'Unspecified error',
        0x81 => 'Malformed Packet',
        0x82 => 'Protocol Error',
        0x83 => 'Implementation specific error',
        0x84 => 'Unsupported Protocol Version',
        0x85 => 'Client Identifier not valid',
        0x86 => 'Bad User Name or Password',
        0x87 => 'Not authorized',
        0x88 => 'Server unavailable',
        0x89 => 'Server busy',
        0x8A => 'Banned',
        0x8B => 'Server shutting down',
        0x8C => 'Bad authentication method',
        0x8D => 'Keep Alive timeout',
        0x8E => 'Session taken over',
        0x8F => 'Topic Filter invalid',
        0x90 => 'Topic Name invalid',
        0x91 => 'Packet Identifier in use',
        0x92 => 'Packet Identifier not found',
        0x93 => 'Receive Maximum exceeded',
        0x94 => 'Topic Alias invalid',
        0x95 => 'Packet too large',
        0x96 => 'Message rate too high',
        0x97 => 'Quota exceeded',
        0x98 => 'Administrative action',
        0x99 => 'Payload format invalid',
        0x9A => 'Retain not supported',
        0x9B => 'QoS not supported',
        0x9C => 'Use another server',
        0x9D => 'Server moved',
        0x9E => 'Shared Subscriptions not supported',
        0x9F => 'Connection rate exceeded',
        0xA0 => 'Maximum connect time',
        0xA1 => 'Subscription Identifiers not supported',
        0xA2 => 'Wildcard Subscriptions not supported',
    ];

    public function description(): string
    {
        if (array_key_exists($this->value, self::DESCRIPTIONS)) {
            return self::DESCRIPTIONS[$this->value];
        }

        return 'Unknown reason code';
    }

    public function isSuccess(): bool
    {
        return $this->value < 0x80;
    }

    public function isError(): bool
    {
        return $this->value >= 0x80;
    }

    /**
     * Convert a reason code to the most specific exception type.
     */
    public function toException(?string $context = null): MqttException
    {
        $message = $context !== null
            ? "$context: {$this->description()} (0x" . dechex($this->value) . ')'
            : "{$this->description()} (0x" . dechex($this->value) . ')';

        return match ($this) {
            self::BadUserNameOrPassword,
            self::NotAuthorized,
            self::BadAuthenticationMethod,
            self::Banned => new AuthenticationError($message, $this->value),

            self::ServerBusy,
            self::ServerShuttingDown,
            self::ServerUnavailable,
            self::UseAnotherServer,
            self::ServerMoved => new ServerError($message, $this->value),

            self::ReceiveMaximumExceeded,
            self::MessageRateTooHigh,
            self::QuotaExceeded,
            self::ConnectionRateExceeded => new QuotaExceeded($message, $this->value),

            self::MalformedPacket,
            self::ProtocolError,
            self::TopicFilterInvalid,
            self::TopicNameInvalid,
            self::TopicAliasInvalid,
            self::PacketTooLarge,
            self::PayloadFormatInvalid => new ProtocolError($message, $this->value),

            default => new MqttException($message, $this->value),
        };
    }

    /**
     * Create a ReasonCode from an integer, returning null if not a known code.
     */
    public static function tryFromInt(int $code): ?self
    {
        return self::tryFrom($code);
    }
}
