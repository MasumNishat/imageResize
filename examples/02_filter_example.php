<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;

/**
 * Example: Image Filters
 *
 * Demonstrates various image filters:
 * - Grayscale, sepia
 * - Blur, sharpen
 * - Brightness, contrast
 * - Artistic effects (emboss, edgedetect, pixelate)
 * - Filter chains (multiple filters)
 */

// Example 1: Grayscale filter
echo "Example 1: Apply grayscale filter\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-grayscale.jpg',
        'grayscale'
    );
    echo "✓ Grayscale filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Sepia tone filter
echo "Example 2: Apply sepia tone filter\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-sepia.jpg',
        'sepia'
    );
    echo "✓ Sepia filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Blur with intensity
echo "Example 3: Apply blur filter with intensity 3\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-blur.jpg',
        'blur',
        ['intensity' => 3]
    );
    echo "✓ Blur filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Sharpen filter
echo "Example 4: Apply sharpen filter\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-sharpen.jpg',
        'sharpen'
    );
    echo "✓ Sharpen filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 5: Brightness adjustment
echo "Example 5: Increase brightness by 50\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-bright.jpg',
        'brightness',
        ['level' => 50]  // -255 to 255
    );
    echo "✓ Brightness filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 6: Contrast adjustment
echo "Example 6: Increase contrast by 30\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-contrast.jpg',
        'contrast',
        ['level' => -30]  // -100 to 100 (negative increases contrast)
    );
    echo "✓ Contrast filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 7: Colorize with blue tint
echo "Example 7: Apply blue colorize tint\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-colorize.jpg',
        'colorize',
        [
            'red' => 0,
            'green' => 0,
            'blue' => 100,
            'alpha' => 0
        ]
    );
    echo "✓ Colorize filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 8: Pixelate effect
echo "Example 8: Apply pixelate effect\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-pixelate.jpg',
        'pixelate',
        ['block_size' => 10]
    );
    echo "✓ Pixelate filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 9: Edge detection
echo "Example 9: Apply edge detection\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-edgedetect.jpg',
        'edgedetect'
    );
    echo "✓ Edge detection filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 10: Emboss effect
echo "Example 10: Apply emboss effect\n";
try {
    imageResize::filter(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-emboss.jpg',
        'emboss'
    );
    echo "✓ Emboss filter applied\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 11: Filter chain - Multiple filters in sequence
echo "Example 11: Apply multiple filters in sequence (grayscale + brightness + contrast)\n";
try {
    imageResize::filterChain(
        __DIR__ . '/../tests/Fixtures/images/test-large.jpg',
        __DIR__ . '/output/filtered-chain.jpg',
        [
            ['type' => 'grayscale'],
            ['type' => 'brightness', 'options' => ['level' => 30]],
            ['type' => 'contrast', 'options' => ['level' => -20]],
            ['type' => 'sharpen']
        ]
    );
    echo "✓ Filter chain applied successfully\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "All filter examples completed!\n";
echo "Check the examples/output/ directory for results.\n";
