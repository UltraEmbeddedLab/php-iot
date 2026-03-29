# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
