<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;

/**
 * Example: Image Cropping
 *
 * Demonstrates various crop modes:
 * - Center crop
 * - Custom position crop
 * - Resize to fill and crop
 */

// Example 1: Center crop to square
echo "Example 1: Center crop to 500x500 square\n";
try {
    imageResize::crop(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/cropped-center.jpg',
        500,  // width
        500   // height
        // x and y are null, so it will center crop
    );
    echo "✓ Center crop successful\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Custom position crop
echo "Example 2: Crop 300x200 from position (100, 50)\n";
try {
    imageResize::crop(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/cropped-custom.jpg',
        300,  // width
        200,  // height
        100,  // x position
        50    // y position
    );
    echo "✓ Custom position crop successful\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Resize to fill and crop (for thumbnails)
echo "Example 3: Resize to fill 400x400 and crop (perfect for thumbnails)\n";
try {
    imageResize::crop(
        __DIR__ . '/../tests/Fixtures/images/test-hd.jpg',
        __DIR__ . '/output/thumbnail.jpg',
        400,  // width
        400,  // height
        null, // x (auto center)
        null, // y (auto center)
        true  // resizeToFill - will resize image to cover dimensions then crop
    );
    echo "✓ Resize and crop successful\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Crop PNG with transparency preservation
echo "Example 4: Crop PNG preserving transparency\n";
try {
    imageResize::crop(
        __DIR__ . '/../tests/Fixtures/images/test-transparent.png',
        __DIR__ . '/output/cropped-transparent.png',
        100,
        100
    );
    echo "✓ PNG crop with transparency successful\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "All crop examples completed!\n";
echo "Check the examples/output/ directory for results.\n";
