# ImageResize Test Suite

Comprehensive test suite for the imageResize PHP library.

## Test Structure

```
tests/
├── Unit/                           # Unit tests
│   ├── ExceptionsTest.php         # Tests for all exception classes
│   ├── ImageResizeTest.php        # Tests for imageResize class methods
│   └── SecurityTest.php           # Security-focused tests
├── Integration/                    # Integration tests
│   └── ImageResizeIntegrationTest.php  # End-to-end scenarios
├── Fixtures/                       # Test data
│   ├── images/                     # Sample images for testing
│   ├── expected/                   # Expected output (future use)
│   └── generate_fixtures.php      # Script to generate test images
└── output/                         # Test output directory (gitignored)
```

## Prerequisites

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Generate test fixtures:**
   ```bash
   php tests/Fixtures/generate_fixtures.php
   ```

## Running Tests

### Run all tests:
```bash
composer test
# or
./vendor/bin/phpunit
```

### Run specific test suite:
```bash
# Unit tests only
./vendor/bin/phpunit tests/Unit

# Integration tests only
./vendor/bin/phpunit tests/Integration

# Specific test file
./vendor/bin/phpunit tests/Unit/SecurityTest.php
```

### Run with code coverage:
```bash
composer test-coverage
```

This generates an HTML coverage report in `coverage/index.html`.

### Run specific test method:
```bash
./vendor/bin/phpunit --filter testPngTransparencyIsPreserved
```

## Test Categories

### Unit Tests

#### ExceptionsTest.php
- Tests all 7 custom exception classes
- Verifies exception hierarchy
- Tests exception messages and codes
- **Total tests: 10**

#### ImageResizeTest.php
- Tests constants and configuration
- Path validation
- File validation
- MIME type support
- Extension detection
- Compression and sizing
- Transparency preservation
- Output validation
- Error handling
- **Total tests: 25+**

#### SecurityTest.php
- Directory traversal prevention
- MIME type validation
- File size limits
- Permission checks
- Secure temp directory creation
- Input sanitization
- Null byte injection prevention
- Symbolic link handling
- **Total tests: 20+**

### Integration Tests

#### ImageResizeIntegrationTest.php
- Real-world scenarios (web optimization, thumbnails)
- Batch processing
- Different target sizes
- Format conversion
- Quality and compression tests
- Transparency preservation
- Performance characteristics
- Edge cases
- **Total tests: 15+**

## Test Coverage Goals

- **Target Coverage: >80%**
- **Current Coverage: Run `composer test-coverage` to see**

### Coverage by Component:
- imageResize class: >90%
- Exception classes: 100%
- Validation methods: >95%
- Compression algorithm: >85%

## Test Fixtures

The test suite uses programmatically generated images:

1. **test-small.jpg** (100x100) - Small JPEG
2. **test-large.jpg** (800x600) - Medium JPEG
3. **test-transparent.png** (200x200) - PNG with transparency
4. **test-solid.png** (300x300) - Solid PNG
5. **test.gif** (150x150) - GIF image
6. **test-tiny.jpg** (50x50) - Very small, low quality
7. **test-hd.jpg** (1920x1080) - Large HD image for compression testing

## Writing New Tests

### Example Unit Test:
```php
public function testMyFeature(): void
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

### Example Exception Test:
```php
public function testThrowsExceptionOnError(): void
{
    $this->expectException(InvalidFileException::class);
    $this->expectExceptionMessage('expected error message');

    imageResize::convert('/nonexistent.jpg', 'output.jpg');
}
```

## Continuous Integration

Tests are automatically run on:
- Every commit
- Pull requests
- Multiple PHP versions (7.2, 7.4, 8.0, 8.1, 8.2, 8.3)
- Multiple operating systems (Linux, macOS, Windows)

## Test Best Practices

1. **Cleanup**: Always clean up test files in `tearDown()`
2. **Isolation**: Each test should be independent
3. **Clear names**: Use descriptive test method names
4. **Assertions**: Use specific assertions (e.g., `assertFileExists` vs `assertTrue(file_exists())`)
5. **Coverage**: Aim for edge cases and error paths
6. **Speed**: Keep tests fast (< 1 second each when possible)

## Troubleshooting

### Tests fail with "Class not found"
```bash
composer dump-autoload
```

### Tests fail with "Fixtures not found"
```bash
php tests/Fixtures/generate_fixtures.php
```

### Permission errors on Linux/Mac
```bash
chmod -R 755 tests/output
```

### Cannot create test images
Check that GD extension is enabled:
```bash
php -m | grep gd
```

## Performance Benchmarks

Typical test execution times:
- Unit tests: ~2-3 seconds
- Integration tests: ~5-7 seconds
- Total suite: ~10 seconds

## Contributing

When contributing, ensure:
1. All tests pass
2. New features have tests
3. Coverage doesn't decrease
4. Tests follow existing patterns

Run quality checks:
```bash
composer check
```

This runs:
- PHPUnit tests
- PHPCS code style check
- PHPStan static analysis

## License

Tests are licensed under Apache 2.0, same as the main library.
