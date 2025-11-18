# image-resize-php

PHP library to resize images to desired file size with intelligent compression.

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.2-8892BF.svg)](https://www.php.net/)
[![CI Status](https://github.com/MasumNishat/imageResize/workflows/CI/badge.svg)](https://github.com/MasumNishat/imageResize/actions)
[![Codecov](https://codecov.io/gh/MasumNishat/imageResize/branch/main/graph/badge.svg)](https://codecov.io/gh/MasumNishat/imageResize)
[![Latest Stable Version](https://img.shields.io/packagist/v/masum-nishat/image-resize-php.svg)](https://packagist.org/packages/masum-nishat/image-resize-php)
[![Total Downloads](https://img.shields.io/packagist/dt/masum-nishat/image-resize-php.svg)](https://packagist.org/packages/masum-nishat/image-resize-php)

---

## Features

✨ **Version 2.2** - Advanced Image Processing

- **Intelligent Compression**: Automatically resize images to meet target file size
- **Security Hardened**: Input validation, path security, MIME type verification
- **Exception Handling**: Comprehensive error handling with custom exceptions
- **PNG Transparency**: Full support for transparent PNG and WebP images
- **Type Safe**: Strict type declarations for PHP 7.2+
- **Well Documented**: Complete PHPDoc comments for all methods
- **Multiple Formats**: Support for JPEG, PNG, GIF, and WebP
- **Quality Control**: Adjustable image quality (0-100)
- **Batch Processing**: Process multiple images efficiently
- **Progress Callbacks**: Track resize operations in real-time
- **Detailed Results**: Get comprehensive resize statistics
- **Image Cropping**: Center, position, and resize-to-fill cropping 🆕
- **Image Filters**: 12+ filters including grayscale, blur, sharpen, artistic effects 🆕
- **Watermarking**: Text and image watermarks with opacity control 🆕

---

## Installation

This package is available through Packagist with the vendor and package identifier the same as this repo.

If using [Composer](https://getcomposer.org/), run following command:

```bash
composer require "masum-nishat/image-resize-php":"^2.0.0"
```

## Requirements

- PHP >= 7.2
- GD extension enabled
- Write permissions for temp directory

> **Note:** This library uses the GD extension which does not support resizing animated GIF files. Only static GIF images are supported.

---

## Quick Start

### Basic Usage

```php
use MasumNishat\imageResize\imageResize;

// Resize image to default 250KB
imageResize::convert('image.jpg', 'image-converted.jpg');

// Extension auto-detection from MIME type
imageResize::convert('image.png', 'image-converted');
```

### Custom Target Size

```php
use MasumNishat\imageResize\imageResize;

// Set target size to 300KB
imageResize::$targetSize = 300000;

imageResize::convert('large-image.jpg', 'compressed-image.jpg');
```

### Custom Temp Directory

```php
use MasumNishat\imageResize\imageResize;

// Use custom temp directory (useful for debugging)
imageResize::$tempDir = '/path/to/custom/temp';

imageResize::convert('image.jpg', 'image-converted.jpg');
```

###  Quality Control (v2.1+)

```php
use MasumNishat\imageResize\imageResize;

// Set custom quality (0-100)
imageResize::$quality = 85; // JPEG/WebP: 85%, PNG: auto-converted

imageResize::convert('image.jpg', 'compressed.jpg');
```

### WebP Support (v2.1+)

```php
use MasumNishat\imageResize\imageResize;

// WebP images are automatically detected and processed
imageResize::convert('image.webp', 'optimized.webp');

// WebP with custom quality
imageResize::$quality = 90;
imageResize::convert('photo.jpg', 'photo.webp'); // Auto-detects from extension
```

### Batch Processing (v2.1+)

```php
use MasumNishat\imageResize\imageResize;

$images = [
    ['source' => 'photo1.jpg', 'target' => 'thumb1.jpg'],
    ['source' => 'photo2.png', 'target' => 'thumb2.png'],
    ['source' => 'photo3.webp', 'target' => 'thumb3.webp'],
];

$results = imageResize::convertBatch($images, function($current, $total, $filename) {
    echo "Processing {$current}/{$total}: {$filename}\n";
});

foreach ($results as $result) {
    if (isset($result['error'])) {
        echo "Error: " . $result['message'] . "\n";
    } else {
        echo $result->getSummary() . "\n";
    }
}
```

### Detailed Results (v2.1+)

```php
use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\ResizeResult;

$result = imageResize::convertWithResult('large.jpg', 'small.jpg');

echo "Original: " . $result->getOriginalDimensionsString() . "\n";
echo "Final: " . $result->getFinalDimensionsString() . "\n";
echo "Compression: " . round($result->getCompressionPercentage(), 1) . "%\n";
echo "Time: " . round($result->processingTime, 2) . "s\n";
echo "Saved: " . $result->formatBytes($result->getSizeReduction()) . "\n";
```

### Progress Callbacks (v2.1+)

```php
use MasumNishat\imageResize\imageResize;

$result = imageResize::convertWithResult(
    'large-video-thumbnail.jpg',
    'optimized.jpg',
    function($progress, $message) {
        echo "[{$progress}%] {$message}\n";
    }
);
```

### Image Cropping (v2.2+)

Crop images to exact dimensions with multiple modes:

```php
use MasumNishat\imageResize\imageResize;

// Center crop to square (perfect for thumbnails)
imageResize::crop(
    'photo.jpg',
    'thumbnail.jpg',
    500,  // width
    500   // height
);

// Custom position crop
imageResize::crop(
    'photo.jpg',
    'cropped.jpg',
    300,  // width
    200,  // height
    100,  // x position
    50    // y position
);

// Resize to fill and crop (best for consistent thumbnails)
imageResize::crop(
    'large-photo.jpg',
    'thumb.jpg',
    400,
    400,
    null,  // auto-center x
    null,  // auto-center y
    true   // resize to fill before cropping
);
```

### Image Filters (v2.2+)

Apply professional image filters:

```php
use MasumNishat\imageResize\imageResize;

// Single filter
imageResize::filter('photo.jpg', 'grayscale.jpg', 'grayscale');

// Filter with options
imageResize::filter(
    'photo.jpg',
    'bright.jpg',
    'brightness',
    ['level' => 50]  // -255 to 255
);

// Multiple filters in sequence
imageResize::filterChain(
    'photo.jpg',
    'artistic.jpg',
    [
        ['type' => 'grayscale'],
        ['type' => 'brightness', 'options' => ['level' => 30]],
        ['type' => 'contrast', 'options' => ['level' => -20]],
        ['type' => 'sharpen']
    ]
);
```

**Available filters**: grayscale, sepia, blur, sharpen, brightness, contrast, colorize, edgedetect, emboss, negate, pixelate, smooth

### Watermarking (v2.2+)

Add text or image watermarks with full control:

```php
use MasumNishat\imageResize\imageResize;

// Text watermark
imageResize::watermarkText(
    'photo.jpg',
    'watermarked.jpg',
    '© 2025 My Brand',
    [
        'position' => 'bottom-right',  // or 'center', 'top-left', etc.
        'font_size' => 12,
        'color' => [255, 255, 255],    // RGB white
        'opacity' => 50,               // 0=opaque, 127=transparent
        'padding' => 10
    ]
);

// Image watermark (logo)
imageResize::watermarkImage(
    'photo.jpg',
    'branded.jpg',
    'logo.png',
    [
        'position' => 'bottom-right',
        'opacity' => 70,   // 0-100
        'scale' => 50,     // 50% of original size
        'padding' => 20
    ]
);

// Custom position watermark
imageResize::watermarkText(
    'photo.jpg',
    'custom.jpg',
    'SAMPLE',
    [
        'x' => 100,
        'y' => 50,
        'font_size' => 20,
        'color' => [255, 0, 0],
        'opacity' => 80
    ]
);
```

---

## Exception Handling

**Version 2.0** introduces comprehensive exception handling for robust error management.

### Available Exceptions

All exceptions extend `ImageResizeException`:

- **`InvalidFileException`** - File doesn't exist, is corrupted, or exceeds size limits
- **`InvalidPathException`** - Invalid or dangerous file paths (e.g., directory traversal)
- **`UnsupportedFormatException`** - Unsupported image format or MIME type mismatch
- **`CompressionFailedException`** - Image compression or processing failed
- **`InsufficientPermissionsException`** - File permission issues
- **`InsufficientDiskSpaceException`** - Not enough disk space for operation

### Error Handling Example

```php
use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;
use MasumNishat\imageResize\Exceptions\InvalidFileException;
use MasumNishat\imageResize\Exceptions\InsufficientDiskSpaceException;

try {
    imageResize::$targetSize = 500000; // 500KB
    imageResize::convert('input.jpg', 'output.jpg');
    echo "Image resized successfully!";

} catch (InvalidFileException $e) {
    echo "Invalid file: " . $e->getMessage();

} catch (InsufficientDiskSpaceException $e) {
    echo "Not enough disk space: " . $e->getMessage();

} catch (ImageResizeException $e) {
    echo "Resize failed: " . $e->getMessage();
}
```

---

## Supported Image Formats

| Format | MIME Type    | Transparency | Quality Control | Version |
|--------|-------------|--------------|-----------------|---------|
| JPEG   | image/jpeg  | No           | 0-100 (default: 100) | 2.0+ |
| PNG    | image/png   | **Yes** ✓    | 0-100 (default: max) | 2.0+ |
| GIF    | image/gif   | **Yes** ✓    | N/A             | 2.0+ |
| **WebP** | **image/webp** | **Yes** ✓ | **0-100 (default: 90)** | **2.1+** 🆕 |

> **Transparency**: Fully preserves alpha channels in PNG, GIF, and WebP
>
> **Quality Control**: v2.1+ allows custom quality settings via `imageResize::$quality`
>
> **GIF Note**: Only static GIF images are supported (no animation)
>
> **WebP**: Requires GD with WebP support (PHP 7.0+ usually includes this)

---

## Security Features

Version 2.0 includes multiple security enhancements:

### Path Validation
- Directory traversal prevention (`../` attacks blocked)
- Real path verification
- File vs directory validation

### File Validation
- MIME type verification using both `getimagesize()` and `finfo`
- File extension validation
- File size limits (max 500MB source, 100MB target)
- Empty file detection

### Permission Checks
- Read permission verification for source files
- Write permission verification for target directories
- Secure temp directory creation with `0700` permissions

### Disk Space Management
- Automatic disk space checks before processing
- Prevents operations that would fill disk

---

## Configuration

### Constants

You can access the following constants for reference:

```php
imageResize::DEFAULT_TARGET_SIZE;      // 250000 (250KB)
imageResize::MIN_TARGET_SIZE;          // 1024 (1KB)
imageResize::MAX_TARGET_SIZE;          // 104857600 (100MB)
imageResize::MAX_SOURCE_FILE_SIZE;     // 524288000 (500MB)
imageResize::JPEG_QUALITY;             // 100
imageResize::PNG_COMPRESSION;          // 9
```

### Limits

- **Minimum target size**: 1KB
- **Maximum target size**: 100MB
- **Maximum source file**: 500MB

---

## How It Works

The library uses an intelligent binary search algorithm to find the optimal image dimensions:

1. **Validation**: Validates file paths, permissions, and formats
2. **Analysis**: Checks if compression is needed
3. **Binary Search**: Iteratively tests different compression levels
4. **Optimization**: Selects the largest image that meets target size
5. **Output**: Saves the optimized image with proper format settings

This approach ensures:
- Minimal quality loss
- Fast processing
- Predictable file sizes

---

## What's New in 2.0

### Critical Fixes
- ✅ Fixed PNG transparency preservation
- ✅ Improved size calculation accuracy
- ✅ Fixed temp directory security

### Security Enhancements
- ✅ Path traversal prevention
- ✅ MIME type validation
- ✅ Permission checks
- ✅ Disk space verification
- ✅ Cryptographically secure temp directories

### Code Quality
- ✅ Strict type declarations (`declare(strict_types=1)`)
- ✅ Comprehensive PHPDoc comments
- ✅ Proper exception handling
- ✅ Constants for magic numbers
- ✅ Improved error messages

### Developer Experience
- ✅ Better error messages
- ✅ Type hints for IDE support
- ✅ Detailed exception information
- ✅ Comprehensive documentation

---

## Upgrading from 1.x to 2.0

### Breaking Changes

1. **Exceptions**: Methods now throw exceptions instead of silent failures
2. **Type Safety**: Strict types may require type casting in some cases
3. **Validation**: Stricter path and file validation may reject previously accepted inputs

### Migration Guide

**Before (v1.x):**
```php
// No error handling
imageResize::convert('image.jpg', 'output.jpg');
```

**After (v2.0):**
```php
// With proper error handling
try {
    imageResize::convert('image.jpg', 'output.jpg');
} catch (ImageResizeException $e) {
    // Handle error
    error_log($e->getMessage());
}
```

---

## Troubleshooting

### "Source file does not exist"
- Verify the file path is correct
- Check file permissions
- Ensure path doesn't contain `../`

### "Unsupported image format"
- Only JPEG, PNG, and GIF are supported
- Verify file is not corrupted
- Check MIME type matches file extension

### "Insufficient disk space"
- Free up disk space (requires 3x source file size + target size)
- Check disk quotas

### "Target directory is not writable"
- Verify write permissions on output directory
- Check directory exists

---

## Examples

### Process Multiple Images

```php
use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;

$images = ['photo1.jpg', 'photo2.png', 'photo3.gif'];

foreach ($images as $image) {
    try {
        imageResize::convert($image, 'compressed_' . $image);
        echo "✓ Processed: $image\n";
    } catch (ImageResizeException $e) {
        echo "✗ Failed: $image - " . $e->getMessage() . "\n";
    }
}
```

### Different Sizes for Different Formats

```php
use MasumNishat\imageResize\imageResize;

// Smaller size for thumbnails
imageResize::$targetSize = 50000; // 50KB
imageResize::convert('profile.jpg', 'thumbnail.jpg');

// Larger size for gallery images
imageResize::$targetSize = 500000; // 500KB
imageResize::convert('gallery.jpg', 'gallery-optimized.jpg');
```

---

## Complete Examples

The `examples/` directory contains comprehensive working examples:

- **01_crop_example.php**: Cropping techniques and modes
- **02_filter_example.php**: All 12 filters with options
- **03_watermark_example.php**: Text and image watermarks
- **04_combined_example.php**: Complete workflows combining features

Run examples:
```bash
php examples/01_crop_example.php
php examples/02_filter_example.php
php examples/03_watermark_example.php
php examples/04_combined_example.php
```

See [examples/README.md](examples/README.md) for detailed documentation.

---

## Contributing

Contributions are welcome! We appreciate your help in making this library better.

### Getting Started

1. Read [CONTRIBUTING.md](CONTRIBUTING.md) for development guidelines
2. Check [CLAUDE.md](CLAUDE.md) for the development roadmap
3. Review [CHANGELOG.md](CHANGELOG.md) for recent changes
4. See [tests/README.md](tests/README.md) for testing guide

### Quick Contribution Guide

```bash
# Fork and clone the repository
git clone https://github.com/YOUR-USERNAME/imageResize.git
cd imageResize

# Install dependencies
composer install

# Generate test fixtures
php tests/Fixtures/generate_fixtures.php

# Run quality checks
composer check

# Make your changes and submit a PR
```

### Reporting Issues

Please report bugs and security issues through the [GitHub issue tracker](https://github.com/MasumNishat/imageResize/issues).

---

## Development Roadmap

See [CLAUDE.md](CLAUDE.md) for the complete development roadmap including:
- ✅ Phase 1: Critical Fixes & Security (Complete)
- ✅ Phase 2: Testing Infrastructure (Complete)
- ✅ Phase 3: Documentation & DevOps (Complete)
- ✅ Phase 4: Feature Enhancements (Complete) - v2.1.0
- ✅ Phase 5: Advanced Features (Complete) - v2.2.0

---

## License

Licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for details.

---

## Author

**Al Masum Nishat**
- Email: masum.nishat21@gmail.com
- GitHub: [@MasumNishat](https://github.com/MasumNishat)

---

## Changelog

### [2.2.0] - 2025-11-18 (Phase 5)

#### Added - Advanced Image Processing
- **Image Cropping**: `crop()` method with center, position, and resize-to-fill modes
- **Image Filters**: `filter()` and `filterChain()` methods with 12+ filters
  - Basic: grayscale, sepia, negate
  - Enhancement: blur, sharpen, brightness, contrast
  - Artistic: emboss, edgedetect, pixelate, smooth
  - Color: colorize with RGB control
- **Watermarking**: `watermarkText()` and `watermarkImage()` methods
  - Text watermarks with font support, color, opacity
  - Image watermarks with scaling, opacity, positioning
  - 5 preset positions + custom coordinates
- **Helper Methods**: `loadImageResource()`, `saveImageResource()`, `getExtensionFromMime()`
- **Examples Directory**: 4 comprehensive example files with README
- Transparency preservation across all new features

### [2.1.0] - 2025-11-18 (Phase 4)

#### Added - Feature Enhancements
- **WebP Support**: Full support for WebP format with transparency
- **Quality Control**: `$quality` property for adjustable compression (0-100)
- **Batch Processing**: `convertBatch()` for processing multiple images
- **Progress Callbacks**: Real-time progress tracking in `convertWithResult()`
- **ResizeResult Class**: Detailed metadata about resize operations
- **EXIF Stubs**: `readExif()` and `writeExif()` for future enhancement
- Enhanced `resize()` method with format-specific quality settings

### [2.0.0] - 2025-11-18 (Phases 1-3)

#### Added
- Comprehensive exception handling system
- PNG transparency preservation
- GIF transparency support
- Path validation and directory traversal prevention
- MIME type validation with double-checking
- File permission checks
- Disk space verification
- Secure temp directory creation
- Type hints and strict types
- Constants for configuration values
- Extensive PHPDoc documentation
- PHPUnit test suite (70+ tests)
- GitHub Actions CI/CD
- PHPCS, PHPStan integration

#### Fixed
- PNG transparency loss during resize
- Size calculation accuracy
- Insecure temp directory creation
- Missing input validation
- Silent error failures

#### Changed
- **BREAKING**: Now throws exceptions instead of silent failures
- **BREAKING**: Strict type enforcement
- Improved error messages
- Better security throughout

#### Security
- Added path traversal prevention
- Added MIME type spoofing protection
- Added file size limits
- Secure random temp directory names
- Permission validation

### [1.1.0] - Previous Release
- Initial stable release
- Basic resize functionality
- PSR-4 autoloading support

---

## Support

If you find this library helpful, please ⭐ star the repository!

For questions or support, please open an issue on GitHub.
