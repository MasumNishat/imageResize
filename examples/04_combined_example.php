<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;

/**
 * Example: Combined Image Processing
 *
 * Demonstrates how to combine multiple features:
 * - Resize + Crop + Filter + Watermark
 * - Creating social media ready images
 * - Product photo processing workflow
 */

echo "=== Combined Image Processing Workflow ===\n\n";

// Workflow 1: Social Media Post
// Resize -> Crop -> Apply Filter -> Add Watermark
echo "Workflow 1: Create Instagram-ready post (1080x1080)\n";
try {
    $sourceImage = __DIR__ . '/../tests/Fixtures/images/test-hd.jpg';
    $step1 = __DIR__ . '/output/workflow-step1.jpg';
    $step2 = __DIR__ . '/output/workflow-step2.jpg';
    $step3 = __DIR__ . '/output/instagram-post.jpg';

    // Step 1: Resize to target file size (for web)
    echo "  Step 1: Resize to 500KB...\n";
    imageResize::$targetSize = 500000; // 500KB
    imageResize::convert($sourceImage, $step1);

    // Step 2: Crop to square 1080x1080
    echo "  Step 2: Crop to 1080x1080 square...\n";
    imageResize::crop(
        $step1 . '.jpg',
        $step2,
        1080,
        1080,
        null,
        null,
        true  // Resize to fill before crop
    );

    // Step 3: Apply warm filter and add watermark
    echo "  Step 3: Apply sepia filter...\n";
    imageResize::filter(
        $step2 . '.jpg',
        $step3,
        'sepia'
    );

    // Step 4: Add text watermark
    echo "  Step 4: Add watermark...\n";
    imageResize::watermarkText(
        $step3 . '.jpg',
        $step3,  // Overwrite
        '@MyBrand',
        [
            'position' => 'bottom-right',
            'font_size' => 12,
            'color' => [255, 255, 255],
            'opacity' => 50,
            'padding' => 20
        ]
    );

    // Clean up intermediate files
    @unlink($step1 . '.jpg');
    @unlink($step2 . '.jpg');

    echo "✓ Instagram post created successfully!\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Workflow 2: Product Photo for E-commerce
// Crop -> Brighten -> Add Logo Watermark
echo "Workflow 2: Create product photo (800x800)\n";
try {
    $sourceImage = __DIR__ . '/../tests/Fixtures/images/test-large.jpg';
    $step1 = __DIR__ . '/output/product-step1.jpg';
    $step2 = __DIR__ . '/output/product-step2.jpg';
    $finalProduct = __DIR__ . '/output/product-photo.jpg';

    // Step 1: Crop to square
    echo "  Step 1: Crop to 800x800...\n";
    imageResize::crop(
        $sourceImage,
        $step1,
        800,
        800,
        null,
        null,
        true
    );

    // Step 2: Enhance with brightness and contrast
    echo "  Step 2: Enhance brightness and contrast...\n";
    imageResize::filterChain(
        $step1 . '.jpg',
        $step2,
        [
            ['type' => 'brightness', 'options' => ['level' => 20]],
            ['type' => 'contrast', 'options' => ['level' => -15]],
            ['type' => 'sharpen']
        ]
    );

    // Step 3: Add subtle corner logo
    echo "  Step 3: Add corner logo...\n";
    // Create a small logo if it doesn't exist
    $logoPath = __DIR__ . '/output/logo.png';
    if (!file_exists($logoPath)) {
        $logo = imagecreatetruecolor(80, 80);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);
        $color = imagecolorallocatealpha($logo, 100, 100, 200, 30);
        imagefilledrectangle($logo, 0, 0, 80, 80, $color);
        imagepng($logo, $logoPath);
        imagedestroy($logo);
    }

    imageResize::watermarkImage(
        $step2 . '.jpg',
        $finalProduct,
        $logoPath,
        [
            'position' => 'top-right',
            'opacity' => 60,
            'scale' => 100,
            'padding' => 15
        ]
    );

    // Clean up intermediate files
    @unlink($step1 . '.jpg');
    @unlink($step2 . '.jpg');

    echo "✓ Product photo created successfully!\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Workflow 3: Thumbnail Gallery
// Create multiple thumbnail sizes with consistent styling
echo "Workflow 3: Create thumbnail gallery (3 sizes)\n";
try {
    $sourceImage = __DIR__ . '/../tests/Fixtures/images/test-hd.jpg';

    $sizes = [
        'small' => 150,
        'medium' => 300,
        'large' => 600
    ];

    foreach ($sizes as $sizeName => $dimension) {
        echo "  Creating {$sizeName} thumbnail ({$dimension}x{$dimension})...\n";

        $output = __DIR__ . "/output/thumb-{$sizeName}.jpg";

        // Crop to size
        imageResize::crop(
            $sourceImage,
            $output,
            $dimension,
            $dimension,
            null,
            null,
            true
        );
    }

    echo "✓ Thumbnail gallery created successfully!\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Workflow 4: Artistic Photo Effect
// Apply multiple filters for artistic effect
echo "Workflow 4: Create artistic photo effect\n";
try {
    $sourceImage = __DIR__ . '/../tests/Fixtures/images/test-large.jpg';
    $output = __DIR__ . '/output/artistic-photo.jpg';

    // Apply artistic filter chain
    echo "  Applying artistic filters...\n";
    imageResize::filterChain(
        $sourceImage,
        $output,
        [
            ['type' => 'contrast', 'options' => ['level' => -30]],
            ['type' => 'brightness', 'options' => ['level' => 10]],
            ['type' => 'colorize', 'options' => [
                'red' => 50,
                'green' => 30,
                'blue' => 0,
                'alpha' => 0
            ]],
            ['type' => 'smooth', 'options' => ['level' => 3]]
        ]
    );

    echo "✓ Artistic photo created successfully!\n\n";
} catch (ImageResizeException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== All combined workflows completed! ===\n";
echo "Check the examples/output/ directory for all results.\n";
