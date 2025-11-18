<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Tests\Integration;

use PHPUnit\Framework\TestCase;
use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\ImageResizeException;

/**
 * Integration tests for end-to-end image processing scenarios
 */
class ImageResizeIntegrationTest extends TestCase
{
    private string $fixturesDir;
    private string $outputDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturesDir = __DIR__ . '/../Fixtures/images/';
        $this->outputDir = __DIR__ . '/../output/';

        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }

        imageResize::$targetSize = imageResize::DEFAULT_TARGET_SIZE;
        imageResize::$tempDir = '';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir($this->outputDir)) {
            $files = glob($this->outputDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    // ========================================
    // Real-World Scenarios
    // ========================================

    public function testResizeHdImageToWebOptimizedSize(): void
    {
        // Simulate resizing a large HD image for web use
        imageResize::$targetSize = 200000; // 200KB - typical for web

        $result = imageResize::convert(
            $this->fixturesDir . 'test-hd.jpg',
            $this->outputDir . 'web-optimized.jpg'
        );

        $this->assertTrue($result);
        $this->assertFileExists($this->outputDir . 'web-optimized.jpg');

        $outputSize = filesize($this->outputDir . 'web-optimized.jpg');

        // Should be under target (with some tolerance)
        $this->assertLessThanOrEqual(imageResize::$targetSize * 1.15, $outputSize);

        // Verify dimensions were reduced
        $originalInfo = getimagesize($this->fixturesDir . 'test-hd.jpg');
        $outputInfo = getimagesize($this->outputDir . 'web-optimized.jpg');

        $this->assertLessThan($originalInfo[0], $outputInfo[0]); // Width reduced
        $this->assertLessThan($originalInfo[1], $outputInfo[1]); // Height reduced

        // Verify aspect ratio maintained
        $originalRatio = $originalInfo[0] / $originalInfo[1];
        $outputRatio = $outputInfo[0] / $outputInfo[1];
        $this->assertEqualsWithDelta($originalRatio, $outputRatio, 0.01);
    }

    public function testCreateThumbnailFromLargeImage(): void
    {
        // Create a small thumbnail
        imageResize::$targetSize = 30000; // 30KB for thumbnail

        imageResize::convert(
            $this->fixturesDir . 'test-large.jpg',
            $this->outputDir . 'thumbnail.jpg'
        );

        $this->assertFileExists($this->outputDir . 'thumbnail.jpg');

        $thumbnailSize = filesize($this->outputDir . 'thumbnail.jpg');
        $this->assertLessThanOrEqual(40000, $thumbnailSize); // Should be small
    }

    public function testBatchProcessingMultipleImages(): void
    {
        $images = [
            'test-small.jpg',
            'test-large.jpg',
            'test-solid.png',
            'test-transparent.png',
            'test.gif',
        ];

        imageResize::$targetSize = 100000; // 100KB for all

        $processed = 0;
        foreach ($images as $image) {
            try {
                $result = imageResize::convert(
                    $this->fixturesDir . $image,
                    $this->outputDir . 'batch_' . $image
                );

                if ($result) {
                    $processed++;
                }
            } catch (ImageResizeException $e) {
                // Log but continue
                $this->fail("Failed to process $image: " . $e->getMessage());
            }
        }

        $this->assertEquals(count($images), $processed);

        // Verify all outputs exist
        foreach ($images as $image) {
            $this->assertFileExists($this->outputDir . 'batch_' . $image);
        }
    }

    public function testDifferentTargetSizesForDifferentPurposes(): void
    {
        $scenarios = [
            'icon' => 10000,      // 10KB for icons
            'thumbnail' => 50000,  // 50KB for thumbnails
            'preview' => 150000,   // 150KB for previews
            'full' => 400000,      // 400KB for full images
        ];

        foreach ($scenarios as $purpose => $targetSize) {
            imageResize::$targetSize = $targetSize;

            imageResize::convert(
                $this->fixturesDir . 'test-hd.jpg',
                $this->outputDir . $purpose . '.jpg'
            );

            $this->assertFileExists($this->outputDir . $purpose . '.jpg');

            $size = filesize($this->outputDir . $purpose . '.jpg');
            $this->assertLessThanOrEqual($targetSize * 1.2, $size);
        }
    }

    // ========================================
    // Format Conversion Scenarios
    // ========================================

    public function testAutoFormatDetectionWorks(): void
    {
        $testCases = [
            ['source' => 'test-small.jpg', 'expected_ext' => '.jpg'],
            ['source' => 'test-solid.png', 'expected_ext' => '.png'],
            ['source' => 'test.gif', 'expected_ext' => '.gif'],
        ];

        foreach ($testCases as $index => $testCase) {
            imageResize::convert(
                $this->fixturesDir . $testCase['source'],
                $this->outputDir . 'auto_' . $index
            );

            $expectedFile = $this->outputDir . 'auto_' . $index . $testCase['expected_ext'];
            $this->assertFileExists($expectedFile);
        }
    }

    // ========================================
    // Quality and Compression Tests
    // ========================================

    public function testAggressiveCompressionForVerySmallTarget(): void
    {
        imageResize::$targetSize = 20000; // 20KB - very aggressive

        imageResize::convert(
            $this->fixturesDir . 'test-hd.jpg',
            $this->outputDir . 'aggressive.jpg'
        );

        $this->assertFileExists($this->outputDir . 'aggressive.jpg');

        $size = filesize($this->outputDir . 'aggressive.jpg');
        $this->assertLessThan(30000, $size);

        // Image should still be valid
        $imageInfo = getimagesize($this->outputDir . 'aggressive.jpg');
        $this->assertNotFalse($imageInfo);
    }

    public function testMinimalCompressionForLargeTarget(): void
    {
        imageResize::$targetSize = 5000000; // 5MB - very large

        imageResize::convert(
            $this->fixturesDir . 'test-large.jpg',
            $this->outputDir . 'minimal-compression.jpg'
        );

        $originalSize = filesize($this->fixturesDir . 'test-large.jpg');
        $outputSize = filesize($this->outputDir . 'minimal-compression.jpg');

        // Should be approximately the same since target is larger than original
        $this->assertEqualsWithDelta($originalSize, $outputSize, $originalSize * 0.1);
    }

    // ========================================
    // Transparency Preservation
    // ========================================

    public function testPngTransparencyFullyPreserved(): void
    {
        imageResize::$targetSize = 100000;

        imageResize::convert(
            $this->fixturesDir . 'test-transparent.png',
            $this->outputDir . 'transparent-preserved.png'
        );

        $img = imagecreatefrompng($this->outputDir . 'transparent-preserved.png');
        $this->assertNotFalse($img);

        // Check that alpha channel is preserved
        imagesavealpha($img, true);
        imagealphablending($img, false);

        // Sample a corner pixel which should be transparent
        $cornerColor = imagecolorat($img, 5, 5);
        $alpha = ($cornerColor & 0x7F000000) >> 24;

        // Alpha should be high (transparent)
        $this->assertGreaterThan(100, $alpha);

        imagedestroy($img);
    }

    // ========================================
    // Error Recovery and Robustness
    // ========================================

    public function testRecoveryFromMultipleOperations(): void
    {
        // Process same image multiple times with different settings
        $settings = [50000, 100000, 200000, 300000];

        foreach ($settings as $index => $targetSize) {
            imageResize::$targetSize = $targetSize;

            $result = imageResize::convert(
                $this->fixturesDir . 'test-large.jpg',
                $this->outputDir . "recovery_test_{$index}.jpg"
            );

            $this->assertTrue($result);
        }

        // All should succeed
        foreach ($settings as $index => $targetSize) {
            $this->assertFileExists($this->outputDir . "recovery_test_{$index}.jpg");
        }
    }

    // ========================================
    // Performance Characteristics
    // ========================================

    public function testProcessingTimeIsReasonable(): void
    {
        $start = microtime(true);

        imageResize::$targetSize = 150000;
        imageResize::convert(
            $this->fixturesDir . 'test-hd.jpg',
            $this->outputDir . 'performance-test.jpg'
        );

        $elapsed = microtime(true) - $start;

        // Should complete in reasonable time (adjust based on system)
        $this->assertLessThan(10, $elapsed, 'Processing took too long');
    }

    // ========================================
    // Image Quality Verification
    // ========================================

    public function testOutputImagesAreValid(): void
    {
        $images = [
            'test-small.jpg' => 'image/jpeg',
            'test-solid.png' => 'image/png',
            'test.gif' => 'image/gif',
        ];

        imageResize::$targetSize = 100000;

        foreach ($images as $source => $expectedMime) {
            imageResize::convert(
                $this->fixturesDir . $source,
                $this->outputDir . 'valid_' . $source
            );

            $imageInfo = getimagesize($this->outputDir . 'valid_' . $source);

            $this->assertNotFalse($imageInfo);
            $this->assertEquals($expectedMime, $imageInfo['mime']);
            $this->assertGreaterThan(0, $imageInfo[0]); // Width
            $this->assertGreaterThan(0, $imageInfo[1]); // Height
        }
    }

    // ========================================
    // Edge Cases
    // ========================================

    public function testVerySmallImageHandling(): void
    {
        imageResize::$targetSize = 100000;

        imageResize::convert(
            $this->fixturesDir . 'test-tiny.jpg',
            $this->outputDir . 'tiny-handled.jpg'
        );

        $this->assertFileExists($this->outputDir . 'tiny-handled.jpg');

        // Should produce valid output
        $imageInfo = getimagesize($this->outputDir . 'tiny-handled.jpg');
        $this->assertNotFalse($imageInfo);
    }

    public function testSquareAndRectangularImages(): void
    {
        $images = [
            'test-small.jpg' => 'square',     // 100x100
            'test-large.jpg' => 'rectangular', // 800x600
        ];

        imageResize::$targetSize = 80000;

        foreach ($images as $source => $type) {
            imageResize::convert(
                $this->fixturesDir . $source,
                $this->outputDir . $type . '.jpg'
            );

            $this->assertFileExists($this->outputDir . $type . '.jpg');

            $imageInfo = getimagesize($this->outputDir . $type . '.jpg');
            $this->assertNotFalse($imageInfo);
        }
    }
}
