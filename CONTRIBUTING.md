# Contributing to ImageResize PHP

Thank you for considering contributing to ImageResize! This document provides guidelines for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

---

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code:

- **Be respectful** and considerate of others
- **Be collaborative** and open to feedback
- **Focus on what is best** for the community
- **Show empathy** towards other community members

---

## Getting Started

### Prerequisites

- PHP >= 7.2
- Composer
- GD extension enabled
- Git

### Quick Start

1. **Fork the repository** on GitHub
2. **Clone your fork**:
   ```bash
   git clone https://github.com/YOUR-USERNAME/imageResize.git
   cd imageResize
   ```
3. **Add upstream remote**:
   ```bash
   git remote add upstream https://github.com/MasumNishat/imageResize.git
   ```
4. **Install dependencies**:
   ```bash
   composer install
   ```
5. **Generate test fixtures**:
   ```bash
   php tests/Fixtures/generate_fixtures.php
   ```

---

## Development Setup

### Install Development Dependencies

```bash
composer install
```

This installs:
- PHPUnit (testing framework)
- PHP_CodeSniffer (code style checker)
- PHPStan (static analysis tool)

### Verify Setup

```bash
# Run all quality checks
composer check

# Or run individually:
composer test        # Run tests
composer phpcs       # Check code style
composer phpstan     # Run static analysis
```

---

## Coding Standards

### PSR-12 Standard

This project follows [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards.

**Key points:**
- Use 4 spaces for indentation (no tabs)
- Use strict types: `declare(strict_types=1);`
- Type hint all parameters and return values
- Maximum line length: 120 characters
- Opening braces on same line for methods/functions

### Check Code Style

```bash
# Check for violations
composer phpcs

# Automatically fix violations
composer phpcs-fix
```

### Static Analysis

Code must pass PHPStan level 8:

```bash
composer phpstan
```

### Documentation

- **All public methods** must have PHPDoc comments
- **Include** `@param`, `@return`, `@throws` tags
- **Describe** what the method does, not how
- **Add examples** for complex functionality

**Example:**
```php
/**
 * Convert and resize an image to the target file size
 *
 * @param string $file Path to the source image file
 * @param string $target Path to the output image file
 * @return bool True on success
 * @throws InvalidFileException If source file is invalid
 * @throws InvalidPathException If file paths are dangerous
 */
public static function convert(string $file, string $target): bool
{
    // Implementation
}
```

---

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Integration

# Run specific test file
./vendor/bin/phpunit tests/Unit/SecurityTest.php

# Run specific test method
./vendor/bin/phpunit --filter testPngTransparencyIsPreserved

# Generate coverage report
composer test-coverage
```

### Writing Tests

**All new features must include tests.**

#### Unit Test Example

```php
public function testNewFeature(): void
{
    imageResize::$targetSize = 100000;

    $result = imageResize::convert(
        $this->fixturesDir . 'test-small.jpg',
        $this->outputDir . 'output.jpg'
    );

    $this->assertTrue($result);
    $this->assertFileExists($this->outputDir . 'output.jpg');
}
```

#### Exception Test Example

```php
public function testThrowsExceptionOnInvalidInput(): void
{
    $this->expectException(InvalidFileException::class);
    $this->expectExceptionMessage('expected message');

    imageResize::convert('invalid.jpg', 'output.jpg');
}
```

### Test Coverage

- **Minimum coverage:** 80%
- **Target coverage:** 90%
- Check coverage: `composer test-coverage`

---

## Submitting Changes

### Branch Naming

Use descriptive branch names:
- `feature/add-webp-support`
- `fix/png-transparency-bug`
- `docs/improve-readme`
- `refactor/compress-method`

### Commit Messages

Write clear, descriptive commit messages:

**Good:**
```
Add WebP format support

- Implement WebP encoding/decoding
- Add WebP MIME type validation
- Include WebP tests
- Update documentation
```

**Bad:**
```
Fixed stuff
```

### Pull Request Process

1. **Create a feature branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**:
   - Write code
   - Add/update tests
   - Update documentation

3. **Run quality checks**:
   ```bash
   composer check
   ```
   All checks must pass!

4. **Commit your changes**:
   ```bash
   git add .
   git commit -m "Clear description of changes"
   ```

5. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create Pull Request**:
   - Go to GitHub
   - Click "New Pull Request"
   - Select your branch
   - Fill out the PR template
   - Link related issues

### Pull Request Requirements

Your PR must:
- ✅ Pass all CI tests (GitHub Actions)
- ✅ Maintain or improve code coverage
- ✅ Follow PSR-12 coding standards
- ✅ Pass PHPStan level 8
- ✅ Include tests for new features
- ✅ Update documentation as needed
- ✅ Have a clear description

### Review Process

1. **Automated checks** run via GitHub Actions
2. **Maintainer review** - may request changes
3. **Address feedback** - push additional commits
4. **Approval** - maintainer approves PR
5. **Merge** - maintainer merges to main branch

---

## Reporting Bugs

### Before Reporting

1. **Check existing issues** - search for duplicates
2. **Verify it's a bug** - not expected behavior
3. **Test on latest version** - bug may be fixed

### Bug Report Template

Create an issue with:

**Title:** Clear, concise description

**Description:**
```markdown
## Bug Description
Clear description of the bug

## Steps to Reproduce
1. Step one
2. Step two
3. Step three

## Expected Behavior
What you expected to happen

## Actual Behavior
What actually happened

## Environment
- PHP Version: 8.2
- ImageResize Version: 2.0.0
- Operating System: Ubuntu 22.04
- GD Version: 2.3.0

## Additional Context
- Screenshots
- Error messages
- Code samples
```

---

## Suggesting Features

### Feature Request Template

Create an issue with:

**Title:** Feature: Brief description

**Description:**
```markdown
## Feature Description
Clear description of the feature

## Use Case
Why this feature is needed

## Proposed Solution
How you think it should work

## Alternatives Considered
Other solutions you've thought about

## Additional Context
Examples, mockups, references
```

### Feature Acceptance Criteria

Features should:
- Align with project goals
- Be well-scoped and defined
- Have clear use cases
- Not break backward compatibility (or justify breaking change)
- Include comprehensive tests
- Include documentation

---

## Development Workflow

### Keeping Your Fork Updated

```bash
# Fetch upstream changes
git fetch upstream

# Merge upstream main into your branch
git checkout main
git merge upstream/main

# Push to your fork
git push origin main
```

### Working on Multiple Features

```bash
# Always branch from main
git checkout main
git pull upstream main

# Create feature branch
git checkout -b feature/new-feature

# When done, create PR from feature branch
```

---

## Code Review Guidelines

When reviewing code:

### What to Look For

- **Correctness**: Does it work as intended?
- **Tests**: Are there adequate tests?
- **Style**: Follows coding standards?
- **Performance**: Any obvious inefficiencies?
- **Security**: Any vulnerabilities?
- **Documentation**: Is it well-documented?

### Providing Feedback

- **Be constructive** and respectful
- **Explain why** changes are needed
- **Suggest solutions** when possible
- **Acknowledge good work**
- **Ask questions** instead of making demands

---

## Release Process

(For maintainers)

1. Update CHANGELOG.md
2. Update version in README
3. Create git tag: `git tag -a v2.1.0 -m "Version 2.1.0"`
4. Push tag: `git push origin v2.1.0`
5. Create GitHub release
6. Publish to Packagist (automatic via webhook)

---

## Additional Resources

- [Development Roadmap](CLAUDE.md) - Full implementation plan
- [Test Documentation](tests/README.md) - Testing guide
- [PSR-12 Standard](https://www.php-fig.org/psr/psr-12/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)

---

## Questions?

If you have questions:
- Open a GitHub issue
- Check existing documentation
- Review closed issues/PRs for similar questions

---

## License

By contributing, you agree that your contributions will be licensed under the Apache License 2.0.

---

**Thank you for contributing to ImageResize! 🎉**
