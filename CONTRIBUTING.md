# Contributing

Thank you for considering contributing to PHP IoT MQTT Client! This document provides guidelines and instructions for contributing.

## Code of Conduct

Please be respectful and constructive in all interactions. We welcome contributors of all experience levels.

## How to Contribute

### Reporting Bugs

Before creating a bug report, please check existing issues to avoid duplicates. When creating a bug report, include:

- A clear, descriptive title
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- PHP version and environment details
- MQTT broker information (if relevant)

### Suggesting Features

Feature suggestions are welcome! Please provide:

- A clear description of the feature
- Use cases and benefits
- Any implementation ideas you have

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run the test suite and ensure all checks pass
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to your branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Development Setup

### Requirements

- PHP 8.4+
- Composer

### Installation

```bash
git clone https://github.com/UltraEmbeddedLab/php-iot.git
cd php-iot
composer install
```

### Running Tests

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Check type coverage
composer type-coverage
```

### Code Style

We use Laravel Pint for code formatting:

```bash
# Check code style
composer pint -- --test

# Fix code style
composer pint
```

### Static Analysis

We use PHPStan at the maximum level:

```bash
composer stan
```

### Code Modernization

We use Rector for code modernization:

```bash
composer rector
```

## Coding Standards

- Follow PSR-12 coding style
- Use strict types (`declare(strict_types=1);`)
- Write descriptive commit messages
- Add tests for new features
- Update documentation when needed
- Keep backwards compatibility in mind

## Testing

- All new features should include tests
- Maintain or improve code coverage
- Use Pest PHP for writing tests
- Tests should be fast and isolated

## Documentation

- Update README.md for user-facing changes
- Add PHPDoc blocks for public methods
- Include examples for new features
- Update CHANGELOG.md following Keep a Changelog format

## Questions?

Feel free to open an issue for any questions about contributing.

Thank you for your contributions!
