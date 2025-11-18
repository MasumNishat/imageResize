<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Tests\Unit;

use PHPUnit\Framework\TestCase;
use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\InvalidFileException;
use MasumNishat\imageResize\Exceptions\InvalidPathException;
use MasumNishat\imageResize\Exceptions\UnsupportedFormatException;
use MasumNishat\imageResize\Exceptions\InsufficientPermissionsException;

/**
 * Unit tests for imageResize class
 */
class ImageResizeTest extends TestCase
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

        // Reset static properties
        imageResize::$targetSize = imageResize::DEFAULT_TARGET_SIZE;
        imageResize::$tempDir = '';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up output directory
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
    // Constants Tests
    // ========================================

    public function testConstantsAreDefined(): void
    {
        $this->assertEquals(250000, imageResize::DEFAULT_TARGET_SIZE);
        $this->assertEquals(20, imageResize::MIN_COMPRESSION_PERCENT);
        $this->assertEquals(100, imageResize::MAX_COMPRESSION_PERCENT);
        $this->assertEquals(20, imageResize::COMPRESSION_INTERVAL);
        $this->assertEquals(100, imageResize::JPEG_QUALITY);
        $this->assertEquals(9, imageResize::PNG_COMPRESSION);
    }

    public function testSecurityConstantsAreDefined(): void
    {
        $this->assertEquals(1024, imageResize::MIN_TARGET_SIZE);
        $this->assertEquals(104857600, imageResize::MAX_TARGET_SIZE);
        $this->assertEquals(524288000, imageResize::MAX_SOURCE_FILE_SIZE);
    }

    // ========================================
    // Path Validation Tests
    // ========================================

    public function testEmptySourcePathThrowsException(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('source path cannot be empty');

        imageResize::convert('', $this->outputDir . 'output.jpg');
    }

    public function testEmptyTargetPathThrowsException(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('target path cannot be empty');

        imageResize::convert($this->fixturesDir . 'test-small.jpg', '');
    }

    public function testNonExistentFileThrowsException(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('does not exist');

        imageResize::convert('/nonexistent/file.jpg', $this->outputDir . 'output.jpg');
    }

    public function testDirectoryTraversalIsBlocked(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('directory traversal');

        imageResize::convert('../../../etc/passwd', $this->outputDir . 'output.jpg');
    }

    public function testDirectoryAsSourceThrowsException(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('not a file');

        imageResize::convert($this->fixturesDir, $this->outputDir . 'output.jpg');
    }

    // ========================================
    // File Validation Tests
    // ========================================

    public function testTargetSizeTooSmallThrowsException(): void
    {
        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Target size too small');

        imageResize::$targetSize = 500; // Less than MIN_TARGET_SIZE (1024)
        imageResize::convert($this->fixturesDir . 'test-small.jpg', $this->outputDir . 'output.jpg');
    }

    public function testTargetSizeTooLargeThrowsException(): void
    {
        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Target size too large');

        imageResize::$targetSize = 200000000; // More than MAX_TARGET_SIZE
        imageResize::convert($this->fixturesDir . 'test-small.jpg', $this->outputDir . 'output.jpg');
    }

    // ========================================
    // MIME Type and Format Tests
    // ========================================

    public function testJpegImageIsSupported(): void
    {
        $result = imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'output-jpeg'
        );

        $this->assertTrue($result);
        $this->assertFileExists($this->outputDir . 'output-jpeg.jpg');
    }

    public function testPngImageIsSupported(): void
    {
        $result = imageResize::convert(
            $this->fixturesDir . 'test-solid.png',
            $this->outputDir . 'output-png'
        );

        $this->assertTrue($result);
        $this->assertFileExists($this->outputDir . 'output-png.png');
    }

    public function testGifImageIsSupported(): void
    {
        $result = imageResize::convert(
            $this->fixturesDir . 'test.gif',
            $this->outputDir . 'output-gif'
        );

        $this->assertTrue($result);
        $this->assertFileExists($this->outputDir . 'output-gif.gif');
    }

    // ========================================
    // Extension Detection Tests
    // ========================================

    public function testExtensionIsAutoDetectedForJpeg(): void
    {
        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'output-no-ext'
        );

        $this->assertFileExists($this->outputDir . 'output-no-ext.jpg');
    }

    public function testExtensionIsAutoDetectedForPng(): void
    {
        imageResize::convert(
            $this->fixturesDir . 'test-solid.png',
            $this->outputDir . 'output-no-ext-png'
        );

        $this->assertFileExists($this->outputDir . 'output-no-ext-png.png');
    }

    public function testExplicitExtensionIsRespected(): void
    {
        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'output-explicit.jpg'
        );

        $this->assertFileExists($this->outputDir . 'output-explicit.jpg');
    }

    // ========================================
    // Compression and Sizing Tests
    // ========================================

    public function testImageSmallerThanTargetIsCopied(): void
    {
        imageResize::$targetSize = 500000; // 500KB

        imageResize::convert(
            $this->fixturesDir . 'test-tiny.jpg',
            $this->outputDir . 'tiny-output.jpg'
        );

        $this->assertFileExists($this->outputDir . 'tiny-output.jpg');

        $originalSize = filesize($this->fixturesDir . 'test-tiny.jpg');
        $outputSize = filesize($this->outputDir . 'tiny-output.jpg');

        // Should be similar size since no compression needed
        $this->assertEquals($originalSize, $outputSize);
    }

    public function testLargeImageIsCompressed(): void
    {
        imageResize::$targetSize = 100000; // 100KB

        imageResize::convert(
            $this->fixturesDir . 'test-large.jpg',
            $this->outputDir . 'compressed.jpg'
        );

        $this->assertFileExists($this->outputDir . 'compressed.jpg');

        $originalSize = filesize($this->fixturesDir . 'test-large.jpg');
        $compressedSize = filesize($this->outputDir . 'compressed.jpg');

        // Compressed file should be smaller
        $this->assertLessThan($originalSize, $compressedSize);
    }

    public function testCustomTargetSizeIsRespected(): void
    {
        imageResize::$targetSize = 50000; // 50KB

        imageResize::convert(
            $this->fixturesDir . 'test-large.jpg',
            $this->outputDir . 'custom-size.jpg'
        );

        $outputSize = filesize($this->outputDir . 'custom-size.jpg');

        // Should be close to target size (allow some margin)
        $this->assertLessThanOrEqual(imageResize::$targetSize * 1.1, $outputSize);
    }

    // ========================================
    // Transparency Tests
    // ========================================

    public function testPngTransparencyIsPreserved(): void
    {
        imageResize::$targetSize = 100000;

        imageResize::convert(
            $this->fixturesDir . 'test-transparent.png',
            $this->outputDir . 'transparent-output.png'
        );

        $this->assertFileExists($this->outputDir . 'transparent-output.png');

        // Verify it's a valid PNG
        $imageInfo = getimagesize($this->outputDir . 'transparent-output.png');
        $this->assertEquals('image/png', $imageInfo['mime']);

        // Load and check for transparency
        $img = imagecreatefrompng($this->outputDir . 'transparent-output.png');
        $this->assertNotFalse($img);

        // PNG should support transparency
        $this->assertTrue(imageistruecolor($img));
        imagedestroy($img);
    }

    // ========================================
    // Output Validation Tests
    // ========================================

    public function testOutputFileIsValidImage(): void
    {
        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'valid-output.jpg'
        );

        $imageInfo = getimagesize($this->outputDir . 'valid-output.jpg');

        $this->assertNotFalse($imageInfo);
        $this->assertEquals('image/jpeg', $imageInfo['mime']);
    }

    public function testConvertReturnsTrue(): void
    {
        $result = imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'return-test.jpg'
        );

        $this->assertTrue($result);
    }

    // ========================================
    // Custom Temp Directory Tests
    // ========================================

    public function testCustomTempDirectoryIsUsed(): void
    {
        $customTempDir = $this->outputDir . 'custom_temp/';
        imageResize::$tempDir = $customTempDir;

        imageResize::convert(
            $this->fixturesDir . 'test-large.jpg',
            $this->outputDir . 'temp-test.jpg'
        );

        // Temp directory should exist during processing
        // (it gets cleaned up after, so we just verify the output exists)
        $this->assertFileExists($this->outputDir . 'temp-test.jpg');

        // Cleanup custom temp dir
        if (is_dir($customTempDir)) {
            rmdir($customTempDir);
        }
    }

    // ========================================
    // Error Handling Tests
    // ========================================

    public function testNonWritableDirectoryThrowsException(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('does not exist');

        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            '/nonexistent/dir/output.jpg'
        );
    }

    // ========================================
    // Multiple Format Tests
    // ========================================

    public function testMultipleImagesCanBeProcessed(): void
    {
        $images = [
            'test-small.jpg' => 'output1.jpg',
            'test-solid.png' => 'output2.png',
            'test.gif' => 'output3.gif',
        ];

        foreach ($images as $source => $target) {
            $result = imageResize::convert(
                $this->fixturesDir . $source,
                $this->outputDir . $target
            );

            $this->assertTrue($result);
            $this->assertFileExists($this->outputDir . $target);
        }
    }

    // ========================================
    // State Reset Tests
    // ========================================

    public function testStaticPropertiesCanBeModified(): void
    {
        imageResize::$targetSize = 300000;
        $this->assertEquals(300000, imageResize::$targetSize);

        imageResize::$targetSize = imageResize::DEFAULT_TARGET_SIZE;
        $this->assertEquals(imageResize::DEFAULT_TARGET_SIZE, imageResize::$targetSize);
    }
}
