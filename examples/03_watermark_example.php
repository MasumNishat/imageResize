<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;

/**
 * Example: Image Watermarking
 *
 * Demonstrates various watermark techniques:
 * - Text watermarks with different positions
 * - Text watermarks with custom fonts
 * - Image watermarks with opacity
 * - Combined watermarking techniques
 */

// Example 1: Simple text watermark (bottom-right)
echo "Example 1: Text watermark at bottom-right (default)\n";
try {
    imageResize::watermarkText(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/watermark-text-br.jpg',
        '© 2025 My Company'
    );
    echo "✓ Text watermark added (bottom-right)\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Text watermark at center
echo "Example 2: Text watermark at center\n";
try {
    imageResize::watermarkText(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/watermark-text-center.jpg',
        'SAMPLE',
        [
            'position' => 'center',
            'font_size' => 20,
            'color' => [255, 0, 0],  // Red
            'opacity' => 80  // Semi-transparent (0=opaque, 127=transparent)
        ]
    );
    echo "✓ Text watermark added (center)\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Text watermark with custom position
echo "Example 3: Text watermark at custom position\n";
try {
    imageResize::watermarkText(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/watermark-text-custom.jpg',
        'Watermark',
        [
            'x' => 50,
            'y' => 50,
            'font_size' => 15,
            'color' => [255, 255, 255],
            'opacity' => 40
        ]
    );
    echo "✓ Text watermark added (custom position)\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Text watermark with TrueType font (if available)
// Note: You would need to provide a TTF font file path
echo "Example 4: Text watermark with TrueType font (skipped - requires TTF file)\n";
echo "To use: Set 'font_file' option to path of a .ttf font file\n\n";

// Example 5: Image watermark (bottom-right)
echo "Example 5: Image watermark at bottom-right\n";
try {
    // First, create a simple watermark image for demonstration
    $watermarkPath = __DIR__ . '/output/logo.png';
    if (!file_exists($watermarkPath)) {
        // Create a simple colored square as logo
        $logo = imagecreatetruecolor(100, 100);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);
        $color = imagecolorallocatealpha($logo, 200, 50, 50, 30);
        imagefilledrectangle($logo, 0, 0, 100, 100, $color);
        imagepng($logo, $watermarkPath);
        imagedestroy($logo);
        echo "  Created sample logo watermark\n";
    }

    imageResize::watermarkImage(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/watermark-image-br.jpg',
        $watermarkPath
    );
    echo "✓ Image watermark added (bottom-right)\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 6: Image watermark at center with opacity
echo "Example 6: Image watermark at center with 50% opacity\n";
try {
    $watermarkPath = __DIR__ . '/output/logo.png';

    imageResize::watermarkImage(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/watermark-image-center.jpg',
        $watermarkPath,
        [
            'position' => 'center',
            'opacity' => 50  // 0-100, where 100 is fully opaque
        ]
    );
    echo "✓ Image watermark added (center, 50% opacity)\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 7: Image watermark scaled and positioned
echo "Example 7: Image watermark scaled to 50% at top-right\n";
try {
    $watermarkPath = __DIR__ . '/output/logo.png';

    imageResize::watermarkImage(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/watermark-image-scaled.jpg',
        $watermarkPath,
        [
            'position' => 'top-right',
            'scale' => 50,  // Scale to 50% of original size
            'padding' => 20
        ]
    );
    echo "✓ Image watermark added (top-right, scaled 50%)\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 8: Watermark on PNG with transparency
echo "Example 8: Text watermark on transparent PNG\n";
try {
    imageResize::watermarkText(
        __DIR__ . '/../tests/Fixtures/images/test-transparent.png',
        __DIR__ . '/output/watermark-png.png',
        'PNG Watermark',
        [
            'position' => 'bottom-right',
            'font_size' => 10,
            'color' => [0, 0, 0],
            'opacity' => 0  // Fully opaque
        ]
    );
    echo "✓ Text watermark added to transparent PNG\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "All watermark examples completed!\n";
echo "Check the examples/output/ directory for results.\n";
