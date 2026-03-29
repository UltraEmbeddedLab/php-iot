# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-03-29

### Added
- **WebSocket Transport** (`WsTransport`): Full RFC 6455 WebSocket support for `ws://` and `wss://` connections with MQTT subprotocol
- **Rate Limiter** (`RateLimiter`): Token bucket client-side rate limiting to prevent broker flooding, configurable via `Options::withRateLimiter()`
- **Offline Message Queue** (`OfflineQueue`): Buffer publishes during disconnects with automatic drain on reconnect, configurable via `Options::withOfflineQueue()`
- **Request/Response Helper** (`RequestResponse`): MQTT 5.0 request/response pattern with automatic correlation data and response topic management
- **MQTT 5.0 Reason Code Enum** (`ReasonCode`): Complete mapping of 40+ MQTT 5.0 reason codes with descriptions and exception conversion
- **Specific Exception Types**: `AuthenticationError`, `ServerError`, `QuotaExceeded` for granular error handling
- **Performance Benchmarks**: `benchmarks/publish-throughput.php` and `benchmarks/encode-decode.php` for measuring encoding performance
- **Integration Tests**: 10 integration tests with Docker Compose + Mosquitto for real broker testing (connect, publish QoS 0/1/2, subscribe, MQTT 5.0 properties)
- **Code Coverage CI**: Codecov integration in GitHub Actions pipeline
- **SECURITY.md**: Vulnerability reporting policy and security best practices
- **Configurable QoS 1 De-duplication**: `Options::withQos1DeduplicationSize()` to tune the duplicate suppression cache (default 256)
- **Full Metrics Instrumentation**: `MetricsInterface` calls throughout Client for connect, disconnect, subscribe, unsubscribe, ping, reconnect, offline queue, and rate limiting

### Changed
- `TransportScheme` enum now includes `WS` and `WSS` variants
- `Options` constructor accepts `qos1DeduplicationSize`, `rateLimiter`, and `offlineQueueSize` parameters
- CI pipeline now uploads code coverage to Codecov

## [1.1.0] - 2026-03-29

### Added
- Topic validation per MQTT spec (`TopicValidator`) with integration in `Client::publish()` and `Client::subscribe()`
- Shared subscriptions helper (`SharedSubscription`) for MQTT 5.0 `$share/{name}/{filter}` support
- Comprehensive test suite: 217 tests (up from 29), covering V5/V311 Encoder+Decoder, FlowControl, TopicAliasManager, Session, Events, and all DTOs
- CI: Rector compliance check and security audit jobs in GitHub Actions
- Composer scripts: `rector:check`, `test:unit`, `test:integration`, `security:audit`, `validate:strict`, `ci`

### Changed
- DTOs converted to `readonly class` (PHP 8.4): `ConnectResult`, `InboundMessage`, `PublishOptions`, `SubscribeResult`, `UnsubscribeResult`, `MessageReceived`, `PacketReceived`, `PacketSent`, `ServerDisconnect`
- V5 Decoder: consolidated duplicate property parsing into `parseAckProperties()` and `decodeQoSAck()`, reducing ~120 lines of duplicated code
- Rector config updated to `RectorConfig::configure()` fluent API
- Rector modernization applied across 33 files (void return types, instanceof checks, early returns)

### Fixed
- **Security**: TLS `verify_peer` and `verify_peer_name` now default to `true` in `TcpTransport::enableTls()` when not explicitly set
- **Security**: `TopicAliasManager::registerAlias()` now cleans up stale bidirectional mappings when reassigning aliases
- **Security**: V5 Encoder type coercion methods (`toUInt16`, `toUInt32`, `toByte`) now throw `InvalidArgumentException` for invalid types (array, resource) instead of silently returning 0
- Message handler exceptions no longer crash the event loop; errors are logged via PSR-3

## [1.0.0] - 2026-01-18

### Added
- Initial release
- MQTT 3.1.1 protocol support
- MQTT 5.0 protocol support with all properties
- TLS/SSL encryption support
- Auto-reconnect with exponential backoff
- QoS 0, 1, 2 support
- Session persistence
- Topic aliases (MQTT 5.0)
- Flow control (MQTT 5.0)
- Shared subscriptions (MQTT 5.0)
- Event-driven architecture with PSR-14 event dispatcher
- PSR-3 logging support
- Easy facade for simple usage
- Comprehensive examples and documentation
