# Session Persistence

Session persistence allows the client to save and restore its session state (subscriptions, pending QoS 2 messages) across restarts.

## Overview

MQTT supports persistent sessions that survive client restarts:

- **MQTT 3.1.1**: `cleanSession=false` to use persistent sessions
- **MQTT 5.0**: `cleanSession=false` + `sessionExpiry > 0`

This library extends broker-side persistence with client-side session storage.

## Configuration

```php
use ScienceStories\Mqtt\Client\Options;
use ScienceStories\Mqtt\Session\FileSessionStore;
use ScienceStories\Mqtt\Protocol\MqttVersion;

// Create session store
$sessionStore = new FileSessionStore('/var/mqtt/sessions', defaultExpirySeconds: 86400);

$options = new Options(
    host: 'broker.example.com',
    version: MqttVersion::V5_0,
)
    ->withClientId('my-device-001')      // Required: stable client ID
    ->withCleanSession(false)            // Required: use persistent sessions
    ->withSessionExpiry(86400)           // MQTT 5.0: 24 hour expiry
    ->withSessionStore($sessionStore);   // Enable client-side persistence
```

## Session State

The following state is persisted:

| Data | Description |
|------|-------------|
| Subscriptions | Topic filters and QoS levels |
| Pending QoS 2 | Packet IDs for incomplete QoS 2 deliveries |
| Timestamp | When the session was last saved |

## FileSessionStore

The built-in `FileSessionStore` saves sessions as JSON files:

```php
use ScienceStories\Mqtt\Session\FileSessionStore;

// Basic usage
$store = new FileSessionStore('/var/mqtt/sessions');

// With automatic expiry
$store = new FileSessionStore('/var/mqtt/sessions', defaultExpirySeconds: 3600);
```

### File Format

```json
{
    "subscriptions": {
        "sensors/+/temp": {"qos": 1, "options": null},
        "alerts/#": {"qos": 2, "options": null}
    },
    "pending_qos2": [1234, 5678],
    "saved_at": 1699999999
}
```

### File Naming

- Simple client IDs (alphanumeric): Used directly as filename
- Complex client IDs: SHA1 hash used for safety

```
/var/mqtt/sessions/my-device-001.json
/var/mqtt/sessions/mqtt_a1b2c3d4e5f6.json  # Hashed
```

## Custom Session Store

Implement `SessionStoreInterface` for custom storage:

```php
use ScienceStories\Mqtt\Contract\SessionStoreInterface;
use ScienceStories\Mqtt\Session\SessionState;

class RedisSessionStore implements SessionStoreInterface
{
    public function save(string $clientId, SessionState $state): void
    {
        $this->redis->set("mqtt:session:{$clientId}", serialize($state));
    }

    public function load(string $clientId): ?SessionState
    {
        $data = $this->redis->get("mqtt:session:{$clientId}");
        return $data ? unserialize($data) : null;
    }

    public function delete(string $clientId): void
    {
        $this->redis->del("mqtt:session:{$clientId}");
    }

    public function exists(string $clientId): bool
    {
        return $this->redis->exists("mqtt:session:{$clientId}");
    }
}
```

## How It Works

### On Connect

1. Client connects with `cleanSession=false`
2. If broker indicates `sessionPresent=true`:
   - Client loads state from session store
   - Subscriptions are restored to internal tracking

### On Disconnect

1. Client calls `disconnect()` (graceful) or connection closes
2. Current session state is saved to session store
3. State includes all active subscriptions and pending QoS 2

### On Reconnect

1. If `sessionPresent=true`, broker has the session
2. Client restores local tracking from session store
3. Both sides resume with consistent state

## SessionState API

```php
use ScienceStories\Mqtt\Session\SessionState;

// Create state manually
$state = new SessionState(
    subscriptions: ['sensors/#' => ['qos' => 1, 'options' => null]],
    pendingQos2: [1234],
    savedAt: time(),
);

// Query state
$state->hasSubscriptions();       // bool
$state->hasPendingQos2();         // bool
$state->getSubscriptionCount();   // int
$state->getAge();                 // int (seconds)
$state->isExpired(3600);          // bool (check against expiry)

// Serialize/deserialize
$array = $state->toArray();
$state = SessionState::fromArray($array);
```

## Best Practices

1. **Stable Client ID**: Use a unique, stable identifier for each device
2. **Set Expiry**: Configure appropriate session expiry for your use case
3. **Secure Storage**: Protect session files from unauthorized access
4. **Clean Up**: Periodically remove expired sessions

```php
// Cleanup expired sessions
$removed = $sessionStore->cleanupExpired();
echo "Removed {$removed} expired sessions";
```

## MQTT Version Differences

| Feature | MQTT 3.1.1 | MQTT 5.0 |
|---------|------------|----------|
| Persistent Session | `cleanSession=false` | `cleanSession=false` |
| Session Expiry | Broker-defined | `sessionExpiry` property |
| Session Present | CONNACK flag | CONNACK flag |

## Example

See `examples/session_persistence_example.php` for a complete working example.

```bash
# First run - creates session and subscriptions
php examples/session_persistence_example.php

# Second run - restores session
php examples/session_persistence_example.php
```
