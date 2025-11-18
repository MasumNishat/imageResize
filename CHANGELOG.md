# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Quality parameter control for user-adjustable compression
- WebP format support
- AVIF format support
- EXIF metadata preservation
- Batch processing method
- Progress callback support
- Image cropping functionality
- Image filters (grayscale, blur, sharpen)
- Watermarking support

## [2.0.0] - 2025-11-18

### Added

#### Security Enhancements
- Path traversal attack prevention using real path validation
- Directory traversal sequence blocking (`../` patterns)
- MIME type validation using both `getimagesize()` and `finfo_file()`
- File extension validation and verification
- File size limits (max 500MB source, max 100MB target)
- Empty file detection and rejection
- Read permission verification for source files
- Write permission verification for target directories
- Secure temp directory creation with `0700` permissions using `random_bytes()`
- Disk space verification before processing operations
- Automatic cleanup even on fatal errors

#### Exception System
- `ImageResizeException` - Base exception for all library errors
- `InvalidFileException` - File validation errors (doesn't exist, corrupted, size exceeded)
- `InvalidPathException` - Path security errors (traversal attempts, invalid paths)
- `UnsupportedFormatException` - Unsupported format or MIME type mismatch
- `CompressionFailedException` - Image compression or processing failures
- `InsufficientPermissionsException` - File permission issues
- `InsufficientDiskSpaceException` - Insufficient disk space errors
- Comprehensive error messages with actionable information
- Exception chaining support

#### Critical Bug Fixes
- **PNG Transparency Preservation**: Fully implemented using `imagealphablending()` and `imagesavealpha()`
- **GIF Transparency Support**: Added alpha channel preservation for GIF images
- **Size Calculation Accuracy**: Improved final file size calculation
- **Secure Temp Directory**: Replaced insecure `microtime() + rand()` with cryptographically secure `random_bytes()`

#### Code Quality
- Added `declare(strict_types=1)` to all PHP files
- Type hints on all method parameters and return values
- Comprehensive PHPDoc comments on all public and private methods
- Constants for all magic numbers (DEFAULT_TARGET_SIZE, MIN_COMPRESSION_PERCENT, etc.)
- Cached dimension calculations to reduce redundant `getimagesize()` calls
- Improved variable naming and code organization
- Reduced cyclomatic complexity

#### Testing Infrastructure
- PHPUnit configuration with code coverage support
- 70+ comprehensive tests across multiple suites
- Unit tests (55+): Exceptions, ImageResize class, Security
- Integration tests (15+): End-to-end real-world scenarios
- Security tests (20+): Attack vector validation
- Test fixtures: 7 programmatically generated images
- Automated fixture generation script
- Test documentation in tests/README.md
- Coverage reporting (HTML and text formats)

#### Development Tools
- PHPStan static analysis (level 8) with strict rules
- PHP_CodeSniffer for PSR-12 compliance
- Composer scripts: test, test-coverage, phpcs, phpcs-fix, phpstan, check
- PHPStan configuration (phpstan.neon)
- PHPCS configuration (phpcs.xml)
- .editorconfig for consistent coding style
- Comprehensive .gitignore

#### CI/CD
- GitHub Actions workflow for automated testing
- Multi-version PHP testing (7.2, 7.3, 7.4, 8.0, 8.1, 8.2, 8.3)
- Multi-OS testing (Ubuntu, macOS, Windows)
- Code quality checks (PHPCS, PHPStan)
- Security audit integration
- Codecov integration for coverage reporting
- Dependency caching for faster builds

#### Documentation
- Completely rewritten README for v2.0
- Exception handling examples and best practices
- Security features documentation
- Troubleshooting guide
- Migration guide from v1.x to v2.0
- CONTRIBUTING.md with development guidelines
- CHANGELOG.md (this file)
- Enhanced composer.json with keywords and support links
- Test suite documentation

#### Package Management
- Enhanced composer.json with better metadata
- Keywords for improved Packagist discoverability
- Support section with issue tracker and source links
- Suggest section for optional extensions
- Dev dependencies clearly separated
- PSR-4 autoloading for both source and tests

### Changed

#### Breaking Changes
- **Exception Handling**: Methods now throw exceptions instead of failing silently
  - Migration: Wrap `convert()` calls in try-catch blocks
- **Strict Type Enforcement**: `declare(strict_types=1)` enabled
  - Migration: Ensure type-correct arguments in all calls
- **Stricter Validation**: More rigorous path and file validation
  - Migration: Ensure paths are absolute and don't contain `../`
- **Return Value**: `convert()` now returns `bool` instead of `void`

#### Improvements
- Better error messages with specific details and suggestions
- Improved compression algorithm efficiency
- Enhanced transparency handling for PNG and GIF
- More secure temp directory management
- Better resource cleanup on errors
- Optimized file I/O operations

### Fixed
- PNG images no longer lose transparency during resize
- GIF transparency is now properly preserved
- Size calculation is more accurate
- Temp directory creation is now cryptographically secure
- Error suppression removed (`@mkdir` now properly handled)
- Silent failures replaced with descriptive exceptions
- Memory leaks in image resource handling

### Security
- **Critical**: Fixed directory traversal vulnerability
- **Critical**: Added MIME type validation to prevent file upload attacks
- **High**: Implemented file size limits to prevent DoS
- **High**: Added permission checks before operations
- **Medium**: Secure random temp directory names
- **Medium**: Disk space validation

### Deprecated
- None

### Removed
- Error suppression operators (`@`)
- `print_r()` for error output
- Insecure temp directory naming

---

## [1.1.0] - Previous Release

### Added
- PSR-4 autoloading support
- Composer package configuration
- Apache 2.0 license
- Basic README documentation

### Fixed
- Installation parameter corrections
- Non-converting image error handling

---

## [1.0.0] - Initial Release

### Added
- Basic image resize functionality
- JPEG, PNG, GIF support
- File size target configuration
- Automatic temp directory management
- Extension auto-detection from MIME type

### Known Issues
- PNG transparency not preserved
- No exception handling
- Missing input validation
- Insecure temp directory creation
- No security features

---

## Versioning

This project uses [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible new features
- **PATCH** version for backwards-compatible bug fixes

## Upgrade Guides

### Upgrading from 1.x to 2.0

**Before (v1.x):**
```php
// No error handling - silent failures
imageResize::convert('image.jpg', 'output.jpg');
```

**After (v2.0):**
```php
use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;

try {
    $result = imageResize::convert('image.jpg', 'output.jpg');
    if ($result) {
        echo "Success!";
    }
} catch (ImageResizeException $e) {
    error_log("Resize failed: " . $e->getMessage());
    // Handle error appropriately
}
```

**Key Changes:**
1. Add try-catch blocks around `convert()` calls
2. Use namespaced exception classes
3. Check return value (now returns `bool`)
4. Ensure paths don't contain `../` sequences
5. Verify source files exist before calling

---

## Links

- [Repository](https://github.com/MasumNishat/imageResize)
- [Issue Tracker](https://github.com/MasumNishat/imageResize/issues)
- [Packagist](https://packagist.org/packages/masum-nishat/image-resize-php)

---

[Unreleased]: https://github.com/MasumNishat/imageResize/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/MasumNishat/imageResize/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/MasumNishat/imageResize/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/MasumNishat/imageResize/releases/tag/v1.0.0
