# ImageResize PHP - Development Roadmap

**Project:** image-resize-php
**Version:** 1.1.0
**Last Updated:** 2025-11-18
**Status:** Development/Audit Phase

---

## Table of Contents
1. [Critical Bug Fixes](#1-critical-bug-fixes)
2. [Security Hardening](#2-security-hardening)
3. [Exception Handling & Validation](#3-exception-handling--validation)
4. [Code Quality & Refactoring](#4-code-quality--refactoring)
5. [Modern PHP Features](#5-modern-php-features)
6. [Testing Infrastructure](#6-testing-infrastructure)
7. [Documentation](#7-documentation)
8. [Core Feature Enhancements](#8-core-feature-enhancements)
9. [Performance Optimizations](#9-performance-optimizations)
10. [Development Tools & CI/CD](#10-development-tools--cicd)
11. [Additional Features](#11-additional-features)
12. [Package Management](#12-package-management)

---

## Implementation Priority Legend
- 🔴 **CRITICAL** - Must fix before production (security/data integrity)
- 🟠 **HIGH** - Important for stability and usability
- 🟡 **MEDIUM** - Quality improvements and features
- 🟢 **LOW** - Nice to have enhancements

---

## 1. Critical Bug Fixes
**Priority:** 🔴 CRITICAL
**Category:** Bug Fixes
**Dependencies:** None
**Estimated Effort:** 2-3 days

### Context
The codebase has two critical bugs that affect core functionality and are explicitly marked as TODOs in the code.

### Tasks

#### 1.1 Fix Final Size Calculation Bug
**File:** `classes/imageResize.php:3`
**Priority:** 🔴 CRITICAL
**Context:** The TODO comment "final size is wrong" indicates the compressed file size doesn't match expectations. This affects the primary purpose of the library.

**Subtasks:**
- [ ] 1.1.1 Investigate current size calculation logic
- [ ] 1.1.2 Add debug logging to track size calculations through compression loop
- [ ] 1.1.3 Identify where size calculation fails
- [ ] 1.1.4 Implement correct size calculation
- [ ] 1.1.5 Add unit tests for size validation
- [ ] 1.1.6 Test with various image formats (JPEG, PNG, GIF)
- [ ] 1.1.7 Remove TODO comment once fixed

**Acceptance Criteria:**
- Final file size is within ±5% of target size
- Works consistently across all supported formats
- Tests pass for edge cases (very small/large images)

---

#### 1.2 Implement PNG Transparency Support
**File:** `classes/imageResize.php:147`
**Priority:** 🔴 CRITICAL
**Context:** PNG images with transparency lose their alpha channel during resize, resulting in black backgrounds.

**Subtasks:**
- [ ] 1.2.1 Research GD transparency preservation methods
- [ ] 1.2.2 Add alpha channel detection
- [ ] 1.2.3 Implement `imagealphablending()` and `imagesavealpha()` for PNG
- [ ] 1.2.4 Preserve transparency in `resize()` method
- [ ] 1.2.5 Test with semi-transparent PNGs
- [ ] 1.2.6 Test with fully transparent PNGs
- [ ] 1.2.7 Update documentation
- [ ] 1.2.8 Remove TODO comment

**Implementation Details:**
```php
// Add before imagecopyresampled():
imagealphablending($tmp, false);
imagesavealpha($tmp, true);
$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
imagefilledrectangle($tmp, 0, 0, $newWidth, $newHeight, $transparent);
```

**Acceptance Criteria:**
- Transparent backgrounds remain transparent
- Semi-transparent pixels maintain alpha values
- No performance degradation

---

## 2. Security Hardening
**Priority:** 🔴 CRITICAL
**Category:** Security
**Dependencies:** Task 3 (Exception Handling)
**Estimated Effort:** 3-4 days

### Context
The library has several security vulnerabilities that could be exploited in production environments.

### Tasks

#### 2.1 Prevent Directory Traversal Attacks
**Priority:** 🔴 CRITICAL
**Context:** User-provided file paths are not validated, allowing potential access to sensitive files via `../../` patterns.

**Subtasks:**
- [ ] 2.1.1 Create `validatePath()` private method
- [ ] 2.1.2 Implement path sanitization using `realpath()`
- [ ] 2.1.3 Verify paths don't escape allowed directories
- [ ] 2.1.4 Add whitelist for allowed directories
- [ ] 2.1.5 Throw exception for invalid paths
- [ ] 2.1.6 Add security tests for path traversal attempts
- [ ] 2.1.7 Document security considerations in README

**Test Cases:**
- `../../etc/passwd` → Exception
- `./image.jpg` → Valid
- Symbolic links → Validate real path

---

#### 2.2 Implement MIME Type Validation
**Priority:** 🔴 CRITICAL
**Context:** Only `getimagesize()` is used, which can be spoofed. Need proper MIME validation.

**Subtasks:**
- [ ] 2.2.1 Add `finfo_file()` for MIME type detection
- [ ] 2.2.2 Create whitelist of allowed MIME types
- [ ] 2.2.3 Validate MIME type matches file extension
- [ ] 2.2.4 Add double-extension detection
- [ ] 2.2.5 Throw exception for invalid file types
- [ ] 2.2.6 Test with spoofed files (e.g., PHP file with .jpg extension)
- [ ] 2.2.7 Add security documentation

**Allowed MIME Types:**
- `image/jpeg`
- `image/png`
- `image/gif`

---

#### 2.3 Add File Permission & Disk Space Checks
**Priority:** 🟠 HIGH
**Context:** No validation that files are readable/writable or that disk space is available.

**Subtasks:**
- [ ] 2.3.1 Check source file is readable
- [ ] 2.3.2 Check target directory is writable
- [ ] 2.3.3 Implement disk space check before resize
- [ ] 2.3.4 Estimate required disk space (source size × 3 for temp files)
- [ ] 2.3.5 Throw appropriate exceptions
- [ ] 2.3.6 Add tests for permission scenarios
- [ ] 2.3.7 Handle cleanup on failure

---

#### 2.4 Secure Temp Directory Creation
**Priority:** 🟠 HIGH
**Context:** `microtime().rand()` is not cryptographically secure and could have collisions.

**Subtasks:**
- [ ] 2.4.1 Replace with `bin2hex(random_bytes(16))`
- [ ] 2.4.2 Use system temp directory as base (`sys_get_temp_dir()`)
- [ ] 2.4.3 Set proper permissions (0700) on temp directory
- [ ] 2.4.4 Add collision detection and retry logic
- [ ] 2.4.5 Ensure cleanup even on fatal errors (register_shutdown_function)
- [ ] 2.4.6 Test temp directory security

---

## 3. Exception Handling & Validation
**Priority:** 🔴 CRITICAL
**Category:** Error Handling
**Dependencies:** None
**Estimated Effort:** 3-4 days

### Context
README states "Exception handling not implemented yet" and the code uses error suppression and print_r for errors.

### Tasks

#### 3.1 Create Custom Exception Classes
**Priority:** 🔴 CRITICAL
**Context:** Need specific exceptions for different error types to allow proper error handling by consumers.

**Subtasks:**
- [ ] 3.1.1 Create `ImageResizeException` base class
- [ ] 3.1.2 Create `InvalidFileException` for file errors
- [ ] 3.1.3 Create `InvalidPathException` for path errors
- [ ] 3.1.4 Create `UnsupportedFormatException` for format errors
- [ ] 3.1.5 Create `CompressionFailedException` for compression errors
- [ ] 3.1.6 Create `InsufficientPermissionsException` for permission errors
- [ ] 3.1.7 Create `InsufficientDiskSpaceException` for disk errors
- [ ] 3.1.8 Add PHPDoc to all exception classes
- [ ] 3.1.9 Create `exceptions/` directory structure

**File Structure:**
```
classes/
  ├── imageResize.php
  └── Exceptions/
      ├── ImageResizeException.php
      ├── InvalidFileException.php
      ├── InvalidPathException.php
      ├── UnsupportedFormatException.php
      ├── CompressionFailedException.php
      ├── InsufficientPermissionsException.php
      └── InsufficientDiskSpaceException.php
```

---

#### 3.2 Implement Input Validation
**Priority:** 🔴 CRITICAL
**Context:** No validation of inputs before processing.

**Subtasks:**
- [ ] 3.2.1 Validate source file exists
- [ ] 3.2.2 Validate source file is readable
- [ ] 3.2.3 Validate target size is positive integer
- [ ] 3.2.4 Validate target size is within reasonable limits (e.g., 1KB - 100MB)
- [ ] 3.2.5 Validate file paths are strings
- [ ] 3.2.6 Validate file is actually an image
- [ ] 3.2.7 Add max file size limit (prevent DoS)
- [ ] 3.2.8 Throw appropriate exceptions for each validation failure

**Validation Rules:**
- Target size: 1024 bytes - 104857600 bytes (100MB)
- Source file max: 500MB
- File must exist and be readable
- Must be valid image format

---

#### 3.3 Replace Error Suppression
**Priority:** 🟠 HIGH
**Context:** `@mkdir()` on line 38 suppresses errors, making debugging impossible.

**Subtasks:**
- [ ] 3.3.1 Remove `@` operator from mkdir
- [ ] 3.3.2 Add try-catch block
- [ ] 3.3.3 Check return value
- [ ] 3.3.4 Throw exception with specific error message
- [ ] 3.3.5 Find and remove all other error suppression operators
- [ ] 3.3.6 Add proper error handling for each

---

#### 3.4 Replace print_r with Exceptions
**Priority:** 🟠 HIGH
**Context:** Line 172 uses `print_r('Unknown image type.')` which outputs to browser/CLI.

**Subtasks:**
- [ ] 3.4.1 Replace print_r with exception throw
- [ ] 3.4.2 Use `UnsupportedFormatException`
- [ ] 3.4.3 Include detected MIME type in error message
- [ ] 3.4.4 Add test for unknown image types

---

## 4. Code Quality & Refactoring
**Priority:** 🟠 HIGH
**Category:** Code Quality
**Dependencies:** Task 3 (Exception Handling)
**Estimated Effort:** 4-5 days

### Context
Code has several anti-patterns, repetition, and poor OOP design that make it hard to maintain and test.

### Tasks

#### 4.1 Refactor from Static to Instance-Based
**Priority:** 🟠 HIGH
**Context:** Everything is static, which is an anti-pattern. Makes testing difficult and creates global state issues.

**Subtasks:**
- [ ] 4.1.1 Create instance properties from static properties
- [ ] 4.1.2 Create constructor for initialization
- [ ] 4.1.3 Convert static methods to instance methods
- [ ] 4.1.4 Keep static `convert()` as convenience wrapper
- [ ] 4.1.5 Update internal method calls
- [ ] 4.1.6 Create fluent interface for configuration
- [ ] 4.1.7 Update documentation with new usage
- [ ] 4.1.8 Maintain backward compatibility with static calls

**New Usage Pattern:**
```php
// Instance-based (new)
$resizer = new ImageResize();
$resizer->setTargetSize(300000)
        ->setTempDir('/custom/temp')
        ->convert('input.jpg', 'output.jpg');

// Static (backward compatible)
ImageResize::convert('input.jpg', 'output.jpg');
```

---

#### 4.2 Extract and Refactor compress() Method
**Priority:** 🟠 HIGH
**Context:** The `compress()` method has deeply nested conditions and repetitive resize/copy logic.

**Subtasks:**
- [ ] 4.2.1 Extract resize-and-test logic into `tryResize()` method
- [ ] 4.2.2 Create binary search algorithm for optimal compression
- [ ] 4.2.3 Replace hardcoded percentages with calculated values
- [ ] 4.2.4 Reduce nesting levels (max 3 levels)
- [ ] 4.2.5 Add early returns for clarity
- [ ] 4.2.6 Add comments explaining algorithm
- [ ] 4.2.7 Create unit tests for compression logic

**Extracted Method:**
```php
private function tryResize(float $compressionPercent): int
{
    // Resize and return file size
}
```

---

#### 4.3 Replace Magic Numbers with Constants
**Priority:** 🟡 MEDIUM
**Context:** Hardcoded values (20, 100, 250000) make code unclear and hard to maintain.

**Subtasks:**
- [ ] 4.3.1 Create class constants for all magic numbers
- [ ] 4.3.2 Define `DEFAULT_TARGET_SIZE = 250000`
- [ ] 4.3.3 Define `MIN_COMPRESSION_PERCENT = 20`
- [ ] 4.3.4 Define `MAX_COMPRESSION_PERCENT = 100`
- [ ] 4.3.5 Define `COMPRESSION_INTERVAL = 20`
- [ ] 4.3.6 Define quality constants for JPEG/PNG
- [ ] 4.3.7 Replace all hardcoded values
- [ ] 4.3.8 Document constants in PHPDoc

---

#### 4.4 Fix Inconsistent Return Values
**Priority:** 🟡 MEDIUM
**Context:** `compress()` returns true/false but value is never used. `convert()` has no return value.

**Subtasks:**
- [ ] 4.4.1 Make `convert()` return boolean (success/failure)
- [ ] 4.4.2 Make `convert()` return result object with metadata
- [ ] 4.4.3 Include compression ratio in result
- [ ] 4.4.4 Include final dimensions in result
- [ ] 4.4.5 Include final file size in result
- [ ] 4.4.6 Update documentation with return value info
- [ ] 4.4.7 Add tests for return values

**Result Object:**
```php
class ResizeResult
{
    public string $targetPath;
    public int $originalSize;
    public int $finalSize;
    public float $compressionRatio;
    public array $originalDimensions;
    public array $finalDimensions;
}
```

---

#### 4.5 Reduce Redundant getimagesize() Calls
**Priority:** 🟡 MEDIUM
**Context:** `getimagesize()` is called multiple times (lines 80, 88, 95, 109) unnecessarily.

**Subtasks:**
- [ ] 4.5.1 Call `getimagesize()` once at start
- [ ] 4.5.2 Store dimensions in instance property
- [ ] 4.5.3 Update all references to use cached value
- [ ] 4.5.4 Only re-call if source image changes
- [ ] 4.5.5 Measure performance improvement

---

## 5. Modern PHP Features
**Priority:** 🟠 HIGH
**Category:** Modernization
**Dependencies:** Task 4 (Refactoring)
**Estimated Effort:** 2-3 days

### Context
Code requires PHP 7.2+ but doesn't use modern PHP features. Should leverage type hints, strict types, and PHP 8 features.

### Tasks

#### 5.1 Add Type Declarations
**Priority:** 🟠 HIGH
**Context:** No type hints on parameters or return types.

**Subtasks:**
- [ ] 5.1.1 Add `declare(strict_types=1)` to all files
- [ ] 5.1.2 Add parameter type hints to all methods
- [ ] 5.1.3 Add return type hints to all methods
- [ ] 5.1.4 Use nullable types where appropriate (`?string`)
- [ ] 5.1.5 Use union types for PHP 8+ (`string|false`)
- [ ] 5.1.6 Add property type declarations
- [ ] 5.1.7 Update PHPDoc to match type hints
- [ ] 5.1.8 Test with strict types enabled

**Example:**
```php
declare(strict_types=1);

public function convert(string $file, string $target): ResizeResult
{
    // ...
}

private function resize(float $newWidth, string $targetFile, string $originalFile): void
{
    // ...
}
```

---

#### 5.2 Use Modern PHP Operators
**Priority:** 🟡 MEDIUM
**Context:** Code could be more concise with modern operators.

**Subtasks:**
- [ ] 5.2.1 Replace ternary operators with null coalescing (`??`)
- [ ] 5.2.2 Use null coalescing assignment (`??=`) where applicable
- [ ] 5.2.3 Consider spaceship operator for comparisons
- [ ] 5.2.4 Use spread operator for array operations
- [ ] 5.2.5 Review and update

---

#### 5.3 Consider Readonly Properties (PHP 8.1+)
**Priority:** 🟢 LOW
**Context:** Some properties shouldn't change after construction.

**Subtasks:**
- [ ] 5.3.1 Identify immutable properties
- [ ] 5.3.2 Add `readonly` keyword for PHP 8.1+
- [ ] 5.3.3 Use constructor property promotion
- [ ] 5.3.4 Document PHP version requirements

---

## 6. Testing Infrastructure
**Priority:** 🟠 HIGH
**Category:** Testing
**Dependencies:** Tasks 1-5 should be started first
**Estimated Effort:** 5-7 days

### Context
Zero test coverage. Need comprehensive test suite before library can be production-ready.

### Tasks

#### 6.1 Set Up PHPUnit
**Priority:** 🟠 HIGH
**Context:** No testing framework installed.

**Subtasks:**
- [ ] 6.1.1 Add PHPUnit to composer dev dependencies
- [ ] 6.1.2 Create `phpunit.xml` configuration
- [ ] 6.1.3 Create `tests/` directory structure
- [ ] 6.1.4 Configure autoloading for tests
- [ ] 6.1.5 Set up code coverage reporting
- [ ] 6.1.6 Add PHPUnit to .gitignore (vendor/)
- [ ] 6.1.7 Document how to run tests in README

**Directory Structure:**
```
tests/
  ├── Unit/
  │   ├── ImageResizeTest.php
  │   └── Exceptions/
  ├── Integration/
  │   └── ImageResizeIntegrationTest.php
  └── Fixtures/
      ├── images/
      │   ├── test.jpg
      │   ├── test.png
      │   ├── test-transparent.png
      │   └── test.gif
      └── expected/
```

---

#### 6.2 Create Unit Tests
**Priority:** 🟠 HIGH
**Context:** Need tests for individual methods and edge cases.

**Subtasks:**
- [ ] 6.2.1 Test `convert()` method with various inputs
- [ ] 6.2.2 Test `compress()` algorithm accuracy
- [ ] 6.2.3 Test `resize()` with different dimensions
- [ ] 6.2.4 Test `getExt()` extension detection
- [ ] 6.2.5 Test temp directory creation and cleanup
- [ ] 6.2.6 Test validation methods
- [ ] 6.2.7 Test exception throwing
- [ ] 6.2.8 Aim for >80% code coverage

---

#### 6.3 Create Integration Tests
**Priority:** 🟠 HIGH
**Context:** Need end-to-end tests with real images.

**Subtasks:**
- [ ] 6.3.1 Test JPEG compression to target size
- [ ] 6.3.2 Test PNG compression with transparency
- [ ] 6.3.3 Test GIF compression
- [ ] 6.3.4 Test very small images (< target size)
- [ ] 6.3.5 Test very large images (> 50MB)
- [ ] 6.3.6 Test corrupted images
- [ ] 6.3.7 Test with different target sizes
- [ ] 6.3.8 Verify output image quality

---

#### 6.4 Create Test Fixtures
**Priority:** 🟠 HIGH
**Context:** Need sample images for testing.

**Subtasks:**
- [ ] 6.4.1 Create/collect test JPEG images (small, medium, large)
- [ ] 6.4.2 Create/collect test PNG images (with/without transparency)
- [ ] 6.4.3 Create/collect test GIF images (static only)
- [ ] 6.4.4 Create corrupted image files for error testing
- [ ] 6.4.5 Create images with odd dimensions
- [ ] 6.4.6 Document fixture files
- [ ] 6.4.7 Keep fixtures small (< 5MB total)

---

#### 6.5 Add Security Tests
**Priority:** 🟠 HIGH
**Context:** Verify security fixes work correctly.

**Subtasks:**
- [ ] 6.5.1 Test path traversal prevention
- [ ] 6.5.2 Test MIME type validation
- [ ] 6.5.3 Test file extension validation
- [ ] 6.5.4 Test with malicious files (PHP disguised as image)
- [ ] 6.5.5 Test permission handling
- [ ] 6.5.6 Test disk space checks

---

## 7. Documentation
**Priority:** 🟡 MEDIUM
**Category:** Documentation
**Dependencies:** Tasks 1-5 for API changes
**Estimated Effort:** 3-4 days

### Context
Documentation is incomplete and doesn't follow modern standards.

### Tasks

#### 7.1 Add PHPDoc Comments
**Priority:** 🟡 MEDIUM
**Context:** No proper docblocks for methods, making IDE support poor.

**Subtasks:**
- [ ] 7.1.1 Add class-level docblock
- [ ] 7.1.2 Add docblocks for all public methods
- [ ] 7.1.3 Add docblocks for all private methods
- [ ] 7.1.4 Document all parameters with `@param`
- [ ] 7.1.5 Document return values with `@return`
- [ ] 7.1.6 Document exceptions with `@throws`
- [ ] 7.1.7 Add usage examples in docblocks
- [ ] 7.1.8 Use `@deprecated` for old methods

---

#### 7.2 Create CHANGELOG.md
**Priority:** 🟡 MEDIUM
**Context:** No version history tracking.

**Subtasks:**
- [ ] 7.2.1 Create CHANGELOG.md following Keep a Changelog format
- [ ] 7.2.2 Document all past versions from git history
- [ ] 7.2.3 Add Unreleased section
- [ ] 7.2.4 Document breaking changes
- [ ] 7.2.5 Link to GitHub releases

**Format:**
```markdown
# Changelog

## [Unreleased]

## [2.0.0] - 2025-XX-XX
### Added
### Changed
### Fixed
### Security
```

---

#### 7.3 Create CONTRIBUTING.md
**Priority:** 🟢 LOW
**Context:** No contribution guidelines.

**Subtasks:**
- [ ] 7.3.1 Create CONTRIBUTING.md
- [ ] 7.3.2 Document code style guidelines
- [ ] 7.3.3 Document testing requirements
- [ ] 7.3.4 Document PR process
- [ ] 7.3.5 Add code of conduct
- [ ] 7.3.6 Document how to report bugs

---

#### 7.4 Improve README.md
**Priority:** 🟡 MEDIUM
**Context:** README has gaps and outdated information.

**Subtasks:**
- [ ] 7.4.1 Remove "development package" warning when ready
- [ ] 7.4.2 Add badges (build status, coverage, version, license)
- [ ] 7.4.3 Add table of contents
- [ ] 7.4.4 Add installation instructions
- [ ] 7.4.5 Add basic usage examples
- [ ] 7.4.6 Add advanced usage examples
- [ ] 7.4.7 Add troubleshooting section
- [ ] 7.4.8 Add FAQ section
- [ ] 7.4.9 Document all configuration options
- [ ] 7.4.10 Add error handling examples
- [ ] 7.4.11 Document system requirements
- [ ] 7.4.12 Add performance benchmarks
- [ ] 7.4.13 Add comparison with alternatives
- [ ] 7.4.14 Update copyright year

---

#### 7.5 Create API Documentation
**Priority:** 🟢 LOW
**Context:** No formal API documentation.

**Subtasks:**
- [ ] 7.5.1 Set up phpDocumentor
- [ ] 7.5.2 Generate API docs
- [ ] 7.5.3 Host on GitHub Pages
- [ ] 7.5.4 Link from README

---

## 8. Core Feature Enhancements
**Priority:** 🟡 MEDIUM
**Category:** Features
**Dependencies:** Tasks 1-4
**Estimated Effort:** 5-7 days

### Context
Missing features that are commonly expected in image resize libraries.

### Tasks

#### 8.1 Add Quality Parameter Control
**Priority:** 🟡 MEDIUM
**Context:** README states "Quality change param is not implemented yet".

**Subtasks:**
- [ ] 8.1.1 Add `$quality` parameter to convert method
- [ ] 8.1.2 Accept quality value (0-100)
- [ ] 8.1.3 Apply quality to JPEG compression
- [ ] 8.1.4 Apply quality to PNG compression (convert to 0-9)
- [ ] 8.1.5 Add validation for quality range
- [ ] 8.1.6 Update documentation
- [ ] 8.1.7 Add tests for quality settings

**API:**
```php
$resizer->setQuality(85)->convert('input.jpg', 'output.jpg');
```

---

#### 8.2 Add WebP Support
**Priority:** 🟡 MEDIUM
**Context:** WebP is a modern format with better compression.

**Subtasks:**
- [ ] 8.2.1 Check for WebP support in GD
- [ ] 8.2.2 Add `image/webp` MIME type support
- [ ] 8.2.3 Add `imagecreatefromwebp()` handling
- [ ] 8.2.4 Add `imagewebp()` output handling
- [ ] 8.2.5 Add quality parameter for WebP
- [ ] 8.2.6 Add tests with WebP images
- [ ] 8.2.7 Document WebP support and requirements

---

#### 8.3 Add Metadata Preservation
**Priority:** 🟡 MEDIUM
**Context:** EXIF data is lost during resize.

**Subtasks:**
- [ ] 8.3.1 Read EXIF data from source image
- [ ] 8.3.2 Store orientation, copyright, camera info
- [ ] 8.3.3 Write EXIF data to output image
- [ ] 8.3.4 Add option to strip metadata (privacy)
- [ ] 8.3.5 Handle images without EXIF gracefully
- [ ] 8.3.6 Add tests for EXIF preservation
- [ ] 8.3.7 Document metadata handling

---

#### 8.4 Add Progress Callback Support
**Priority:** 🟢 LOW
**Context:** No way to track compression progress for large images.

**Subtasks:**
- [ ] 8.4.1 Add callback parameter to convert method
- [ ] 8.4.2 Call callback at each compression step
- [ ] 8.4.3 Pass progress percentage to callback
- [ ] 8.4.4 Pass current file size to callback
- [ ] 8.4.5 Add example usage in documentation
- [ ] 8.4.6 Add tests with mock callbacks

**Usage:**
```php
$resizer->convert('input.jpg', 'output.jpg', function($progress) {
    echo "Progress: {$progress}%\n";
});
```

---

#### 8.5 Add Batch Processing
**Priority:** 🟢 LOW
**Context:** Can't process multiple images efficiently.

**Subtasks:**
- [ ] 8.5.1 Create `convertBatch()` method
- [ ] 8.5.2 Accept array of source/target pairs
- [ ] 8.5.3 Process images sequentially
- [ ] 8.5.4 Return array of results
- [ ] 8.5.5 Handle partial failures gracefully
- [ ] 8.5.6 Add progress callback for batch
- [ ] 8.5.7 Add tests for batch processing

---

## 9. Performance Optimizations
**Priority:** 🟡 MEDIUM
**Category:** Performance
**Dependencies:** Tasks 1-4
**Estimated Effort:** 3-4 days

### Context
Current compression algorithm is inefficient and creates unnecessary file I/O.

### Tasks

#### 9.1 Optimize Compression Algorithm
**Priority:** 🟡 MEDIUM
**Context:** Current binary search is naive and creates many temp files.

**Subtasks:**
- [ ] 9.1.1 Research optimal compression prediction algorithms
- [ ] 9.1.2 Implement predictive sizing based on dimensions/format
- [ ] 9.1.3 Reduce number of compression attempts
- [ ] 9.1.4 Use true binary search instead of fixed intervals
- [ ] 9.1.5 Benchmark performance improvements
- [ ] 9.1.6 Document algorithm in code comments

**Goal:** Reduce from 3-12 attempts to 3-5 attempts on average.

---

#### 9.2 Reduce File I/O Operations
**Priority:** 🟡 MEDIUM
**Context:** Three temp files created per compression cycle.

**Subtasks:**
- [ ] 9.2.1 Reuse single temp file instead of three
- [ ] 9.2.2 Store image resource in memory when possible
- [ ] 9.2.3 Only write to disk when necessary
- [ ] 9.2.4 Measure memory usage impact
- [ ] 9.2.5 Add memory limit checks

---

#### 9.3 Add Caching for Dimension Calculations
**Priority:** 🟢 LOW
**Context:** Same calculations repeated multiple times.

**Subtasks:**
- [ ] 9.3.1 Cache aspect ratio calculation
- [ ] 9.3.2 Cache dimension calculations
- [ ] 9.3.3 Measure performance improvement

---

## 10. Development Tools & CI/CD
**Priority:** 🟡 MEDIUM
**Category:** DevOps
**Dependencies:** Task 6 (Testing)
**Estimated Effort:** 2-3 days

### Context
No automated code quality checks or CI/CD pipeline.

### Tasks

#### 10.1 Add Code Quality Tools
**Priority:** 🟡 MEDIUM
**Context:** No linting or static analysis.

**Subtasks:**
- [ ] 10.1.1 Add PHP_CodeSniffer (phpcs) to dev dependencies
- [ ] 10.1.2 Configure PSR-12 coding standard
- [ ] 10.1.3 Add PHPStan for static analysis (level 8)
- [ ] 10.1.4 Add Psalm as alternative static analyzer
- [ ] 10.1.5 Add PHP CS Fixer for automatic formatting
- [ ] 10.1.6 Create composer scripts for quality checks
- [ ] 10.1.7 Document in CONTRIBUTING.md

**Composer Scripts:**
```json
{
    "scripts": {
        "test": "phpunit",
        "phpcs": "phpcs --standard=PSR12 classes/",
        "phpstan": "phpstan analyze -l 8 classes/",
        "fix": "php-cs-fixer fix classes/"
    }
}
```

---

#### 10.2 Set Up GitHub Actions
**Priority:** 🟡 MEDIUM
**Context:** No continuous integration.

**Subtasks:**
- [ ] 10.2.1 Create `.github/workflows/ci.yml`
- [ ] 10.2.2 Run tests on PHP 7.2, 7.4, 8.0, 8.1, 8.2, 8.3
- [ ] 10.2.3 Run on Linux, macOS, Windows
- [ ] 10.2.4 Generate code coverage report
- [ ] 10.2.5 Upload coverage to Codecov
- [ ] 10.2.6 Run PHPStan/Psalm
- [ ] 10.2.7 Run PHPCS
- [ ] 10.2.8 Add status badges to README

---

#### 10.3 Add Pre-commit Hooks
**Priority:** 🟢 LOW
**Context:** No automated checks before commits.

**Subtasks:**
- [ ] 10.3.1 Create `.git/hooks/pre-commit` script
- [ ] 10.3.2 Run PHPCS on staged files
- [ ] 10.3.3 Run PHPStan on staged files
- [ ] 10.3.4 Run relevant tests
- [ ] 10.3.5 Document how to install hooks

---

#### 10.4 Add .editorconfig
**Priority:** 🟢 LOW
**Context:** Ensure consistent coding style across editors.

**Subtasks:**
- [ ] 10.4.1 Create `.editorconfig` file
- [ ] 10.4.2 Set indent style (spaces)
- [ ] 10.4.3 Set indent size (4)
- [ ] 10.4.4 Set line endings (LF)
- [ ] 10.4.5 Set charset (UTF-8)
- [ ] 10.4.6 Document in README

---

## 11. Additional Features
**Priority:** 🟢 LOW
**Category:** Enhancements
**Dependencies:** Tasks 1-8
**Estimated Effort:** 5-7 days

### Context
Nice-to-have features that enhance library capabilities.

### Tasks

#### 11.1 Add Crop Functionality
**Priority:** 🟢 LOW
**Context:** Only resize available, no crop to exact dimensions.

**Subtasks:**
- [ ] 11.1.1 Create `crop()` method
- [ ] 11.1.2 Support center crop
- [ ] 11.1.3 Support custom crop positions
- [ ] 11.1.4 Support crop with resize
- [ ] 11.1.5 Add tests
- [ ] 11.1.6 Document crop functionality

---

#### 11.2 Add Format Conversion
**Priority:** 🟢 LOW
**Context:** Can't convert between formats independently.

**Subtasks:**
- [ ] 11.2.1 Create `convertFormat()` method
- [ ] 11.2.2 Allow JPEG → PNG conversion
- [ ] 11.2.3 Allow PNG → JPEG conversion (handle transparency)
- [ ] 11.2.4 Allow conversion to WebP
- [ ] 11.2.5 Add tests
- [ ] 11.2.6 Document format conversion

---

#### 11.3 Add Image Filters
**Priority:** 🟢 LOW
**Context:** No blur, sharpen, brightness adjustments.

**Subtasks:**
- [ ] 11.3.1 Add grayscale filter
- [ ] 11.3.2 Add blur filter
- [ ] 11.3.3 Add sharpen filter
- [ ] 11.3.4 Add brightness adjustment
- [ ] 11.3.5 Add contrast adjustment
- [ ] 11.3.6 Chain filters together
- [ ] 11.3.7 Add tests
- [ ] 11.3.8 Document filters

---

#### 11.4 Add Watermarking
**Priority:** 🟢 LOW
**Context:** Common requirement not supported.

**Subtasks:**
- [ ] 11.4.1 Create `addWatermark()` method
- [ ] 11.4.2 Support text watermarks
- [ ] 11.4.3 Support image watermarks
- [ ] 11.4.4 Support positioning (corner, center, custom)
- [ ] 11.4.5 Support opacity control
- [ ] 11.4.6 Add tests
- [ ] 11.4.7 Document watermarking

---

#### 11.5 Add AVIF Support
**Priority:** 🟢 LOW
**Context:** Next-gen image format, better compression than WebP.

**Subtasks:**
- [ ] 11.5.1 Check GD/Imagick support for AVIF
- [ ] 11.5.2 Add AVIF read support
- [ ] 11.5.3 Add AVIF write support
- [ ] 11.5.4 Add quality parameter
- [ ] 11.5.5 Add tests
- [ ] 11.5.6 Document AVIF support

---

#### 11.6 Add Logging
**Priority:** 🟢 LOW
**Context:** Can't debug production issues.

**Subtasks:**
- [ ] 11.6.1 Add PSR-3 logger interface support
- [ ] 11.6.2 Log compression attempts
- [ ] 11.6.3 Log file sizes at each step
- [ ] 11.6.4 Log errors and warnings
- [ ] 11.6.5 Add optional verbose mode
- [ ] 11.6.6 Document logging configuration

---

## 12. Package Management
**Priority:** 🟢 LOW
**Category:** Packaging
**Dependencies:** None
**Estimated Effort:** 1 day

### Context
Package configuration could be improved for better discoverability and development.

### Tasks

#### 12.1 Improve composer.json
**Priority:** 🟢 LOW
**Context:** Missing keywords, suggestions, and dev dependencies.

**Subtasks:**
- [ ] 12.1.1 Add keywords for discoverability
- [ ] 12.1.2 Add `suggest` section for extensions
- [ ] 12.1.3 Add dev dependencies (PHPUnit, PHPCS, PHPStan)
- [ ] 12.1.4 Specify tested PHP versions
- [ ] 12.1.5 Add scripts section
- [ ] 12.1.6 Add support section with links

**Keywords:**
- image
- resize
- compression
- thumbnail
- gd
- php

**Suggests:**
```json
"suggest": {
    "ext-gd": "Required for image manipulation",
    "ext-exif": "Required for EXIF metadata preservation"
}
```

---

#### 12.2 Improve .gitignore
**Priority:** 🟢 LOW
**Context:** Currently empty, should ignore common files.

**Subtasks:**
- [ ] 12.2.1 Ignore `vendor/` directory
- [ ] 12.2.2 Ignore IDE files (.idea/, .vscode/, *.sublime-*)
- [ ] 12.2.3 Ignore OS files (.DS_Store, Thumbs.db)
- [ ] 12.2.4 Ignore composer.lock (for libraries)
- [ ] 12.2.5 Ignore coverage reports
- [ ] 12.2.6 Ignore temp/cache directories

---

#### 12.3 Add Semantic Versioning
**Priority:** 🟢 LOW
**Context:** Ensure version numbers follow semver.

**Subtasks:**
- [ ] 12.3.1 Document semver compliance
- [ ] 12.3.2 Plan version 2.0.0 for breaking changes
- [ ] 12.3.3 Add git tags for releases
- [ ] 12.3.4 Create GitHub releases with notes

---

## Implementation Roadmap

### Phase 1: Critical Fixes & Security (Week 1-2)
**Goal:** Make library production-ready for basic use

1. Critical Bug Fixes (Task 1)
2. Security Hardening (Task 2)
3. Exception Handling (Task 3)

**Deliverable:** Version 1.2.0 - Production-ready with critical fixes

---

### Phase 2: Code Quality & Testing (Week 3-4)
**Goal:** Improve maintainability and add test coverage

4. Code Quality Refactoring (Task 4)
5. Modern PHP Features (Task 5)
6. Testing Infrastructure (Task 6)

**Deliverable:** Version 1.3.0 - Fully tested with >80% coverage

---

### Phase 3: Documentation & DevOps (Week 5)
**Goal:** Professional documentation and automated checks

7. Documentation (Task 7)
10. Development Tools & CI/CD (Task 10)
12. Package Management (Task 12)

**Deliverable:** Version 1.4.0 - Professional documentation and CI/CD

---

### Phase 4: Feature Enhancements (Week 6-7)
**Goal:** Add commonly requested features

8. Core Feature Enhancements (Task 8)
9. Performance Optimizations (Task 9)

**Deliverable:** Version 2.0.0 - Feature-rich with breaking changes

---

### Phase 5: Advanced Features (Week 8+)
**Goal:** Nice-to-have enhancements

11. Additional Features (Task 11)

**Deliverable:** Version 2.1.0+ - Full-featured image manipulation library

---

## Summary Statistics

- **Total Tasks:** 12 main categories
- **Total Subtasks:** 200+ individual items
- **Estimated Total Effort:** 30-40 days
- **Priority Breakdown:**
  - 🔴 Critical: 15 items
  - 🟠 High: 28 items
  - 🟡 Medium: 35 items
  - 🟢 Low: 40+ items

---

## Quick Start Guide

### For Immediate Production Use
Focus on Phase 1 tasks:
1. Fix PNG transparency (1.2)
2. Add input validation (3.2)
3. Add security checks (2.1, 2.2)
4. Add basic exception handling (3.1, 3.2)

### For Long-term Development
Follow the full roadmap phases 1-5 in order.

### For Contributing
Start with:
1. Set up development environment (10.1)
2. Add tests for existing features (6.2)
3. Fix one critical bug (1.1 or 1.2)
4. Submit PR

---

## Notes

- All file paths are relative to project root
- Follow PSR-12 coding standards
- Maintain backward compatibility where possible
- Breaking changes require major version bump
- All new code requires tests
- All public APIs require documentation

---

**Last Updated:** 2025-11-18
**Maintained By:** Claude Development Team
**Status:** Active Development
