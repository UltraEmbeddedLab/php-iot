# PHP IoT MQTT Client

[![CI](https://github.com/UltraEmbeddedLab/php-iot/actions/workflows/ci.yml/badge.svg)](https://github.com/UltraEmbeddedLab/php-iot/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/science-stories/php-iot/v)](https://packagist.org/packages/science-stories/php-iot)
[![License](https://poser.pugx.org/science-stories/php-iot/license)](https://packagist.org/packages/science-stories/php-iot)
[![PHP Version](https://img.shields.io/packagist/php-v/science-stories/php-iot)](https://packagist.org/packages/science-stories/php-iot)

Modern, production-grade MQTT 3.1.1 & 5.0 client for PHP 8.4+

## Features

- **Modern PHP 8.4+** with strict types and modern syntax
- **MQTT 3.1.1 & 5.0** protocol support
- **TLS/SSL** encryption support
- **Auto-reconnect** with exponential backoff
- **QoS 0, 1, 2** support
- **Session persistence** for reliable message delivery
- **Topic aliases** (MQTT 5.0)
- **Flow control** (MQTT 5.0)
- **Shared subscriptions** (MQTT 5.0)
- **PSR-3** logging support
- **PSR-14** event dispatcher support

## Requirements

- PHP 8.4 or higher
- `ext-sockets` extension
- `ext-openssl` extension (for TLS)

## Installation

Install via Composer:

```bash
composer require science-stories/php-iot
```

## Quick Start

### Simple Publish (Fire and Forget)

The easiest way to publish a message:

```php
use ScienceStories\Mqtt\Easy\Mqtt;

Mqtt::publish(
    host: 'broker.example.com',
    topic: 'sensors/temperature',
    payload: '23.5',
);
```

### Publish with TLS and Authentication

```php
use ScienceStories\Mqtt\Easy\Mqtt;

Mqtt::publish(
    host: 'broker.example.com',
    topic: 'sensors/temperature',
    payload: '23.5',
    tls: true,
    username: 'user',
    password: 'secret',
);
```

### Using MQTT 5.0

```php
use ScienceStories\Mqtt\Easy\Mqtt;
use ScienceStories\Mqtt\Protocol\QoS;

Mqtt::publish(
    host: 'broker.example.com',
    topic: 'sensors/temperature',
    payload: '23.5',
    version: 'v5',
    qos: QoS::AtLeastOnce,
    properties: [
        'message_expiry_interval' => 3600,
        'content_type' => 'text/plain',
    ],
);
```

### Subscribe to Topics

For more complex use cases, use the full client:

```php
use ScienceStories\Mqtt\Client\Client;
use ScienceStories\Mqtt\Client\Options;
use ScienceStories\Mqtt\Protocol\MqttVersion;
use ScienceStories\Mqtt\Transport\TcpTransport;

$options = new Options(
    host: 'broker.example.com',
    port: 1883,
    version: MqttVersion::V5_0,
);

$options = $options
    ->withClientId('my-client')
    ->withKeepAlive(60)
    ->withCleanSession(true);

$client = new Client($options, new TcpTransport());
$client->connect();

// Subscribe to topics
$client->subscribe([
    ['filter' => 'sensors/#', 'qos' => 1],
]);

// Handle incoming messages
$client->onMessage(function ($message) {
    echo "Received: {$message->payload} on {$message->topic}\n";
});

// Listen for messages
while (true) {
    $client->loopOnce(1.0);
}
```

### Long-Running Connection

Use the `Mqtt::connect()` method for sessions that need to publish multiple messages:

```php
use ScienceStories\Mqtt\Easy\Mqtt;
use ScienceStories\Mqtt\Client\PublishOptions;
use ScienceStories\Mqtt\Protocol\QoS;

$client = Mqtt::connect(
    host: 'broker.example.com',
    port: 1883,
    version: 'v5',
);

// Publish multiple messages
$client->publish('sensors/temp', '23.5', new PublishOptions(qos: QoS::AtLeastOnce));
$client->publish('sensors/humidity', '65', new PublishOptions(qos: QoS::AtLeastOnce));

$client->disconnect();
```

## Configuration Options

### Client Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `host` | string | required | MQTT broker hostname |
| `port` | int | 1883/8883 | Broker port (auto-detected based on TLS) |
| `version` | MqttVersion | V3_1_1 | MQTT protocol version |
| `clientId` | string | auto | Client identifier |
| `keepAlive` | int | 60 | Keep alive interval in seconds |
| `cleanSession` | bool | true | Start with clean session |
| `username` | string | null | Authentication username |
| `password` | string | null | Authentication password |

### Publish Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `qos` | QoS | AtMostOnce | Quality of Service level |
| `retain` | bool | false | Retain message on broker |
| `properties` | array | null | MQTT 5.0 properties |

### TLS Configuration

```php
$options = $options->withTls([
    'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
        'cafile' => '/path/to/ca.crt',
        'local_cert' => '/path/to/client.crt',
        'local_pk' => '/path/to/client.key',
    ],
]);
```

## MQTT 5.0 Features

### Topic Aliases

Reduce bandwidth by using numeric aliases for frequently used topics:

```php
$client->publish('long/topic/name', 'data', new PublishOptions(
    properties: ['topic_alias' => 1],
));
```

### Message Expiry

Set expiration time for messages:

```php
$client->publish('alerts/warning', 'Alert!', new PublishOptions(
    properties: ['message_expiry_interval' => 300], // 5 minutes
));
```

### User Properties

Attach custom metadata to messages:

```php
$client->publish('events/user', $payload, new PublishOptions(
    properties: [
        'user_properties' => [
            'source' => 'web-app',
            'version' => '1.0',
        ],
    ],
));
```

## Documentation

Detailed documentation is available in the `docs/` directory:

- [Flow Control](docs/flow-control.md)
- [Session Persistence](docs/session-persistence.md)
- [Shared Subscriptions](docs/shared-subscriptions.md)
- [Topic Aliases](docs/topic-aliases.md)
- [Server Disconnect](docs/server-disconnect.md)

## Examples

Check the `examples/` directory for complete working examples:

- Basic connect/publish/subscribe
- MQTT 3.1.1 and 5.0 examples
- QoS demonstrations
- TLS connections
- Advanced features

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Static analysis
composer stan

# Code style
composer pint
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

PHP IoT MQTT Client is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits

Developed by [Bogdan Gewald](mailto:gewaldb@gmail.com)
