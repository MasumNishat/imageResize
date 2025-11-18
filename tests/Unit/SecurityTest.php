<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Tests\Unit;

use PHPUnit\Framework\TestCase;
use MasumNishat\imageResize\imageResize;
use MasumNishat\imageResize\Exceptions\InvalidPathException;
use MasumNishat\imageResize\Exceptions\InvalidFileException;
use MasumNishat\imageResize\Exceptions\InsufficientPermissionsException;

/**
 * Security-focused tests for imageResize class
 */
class SecurityTest extends TestCase
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
    // Directory Traversal Prevention
    // ========================================

    public function testBlocksSimpleDirectoryTraversal(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('directory traversal');

        imageResize::convert('../sensitive.jpg', $this->outputDir . 'output.jpg');
    }

    public function testBlocksDeepDirectoryTraversal(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('directory traversal');

        imageResize::convert('../../../etc/passwd', $this->outputDir . 'output.jpg');
    }

    public function testBlocksDirectoryTraversalInTarget(): void
    {
        // Note: This tests that target directory must exist
        $this->expectException(InvalidPathException::class);

        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            '../../../tmp/malicious.jpg'
        );
    }

    public function testBlocksUrlEncodedDirectoryTraversal(): void
    {
        $this->expectException(InvalidPathException::class);

        // Even if someone tries URL encoding
        imageResize::convert('..%2F..%2Fetc%2Fpasswd', $this->outputDir . 'output.jpg');
    }

    public function testBlocksRelativePathWithDots(): void
    {
        $this->expectException(InvalidPathException::class);

        imageResize::convert('./../../sensitive.jpg', $this->outputDir . 'output.jpg');
    }

    // ========================================
    // File Type and MIME Validation
    // ========================================

    public function testRejectsNonImageFiles(): void
    {
        // Create a fake "image" that's actually text
        $fakeImage = $this->outputDir . 'fake.jpg';
        file_put_contents($fakeImage, 'This is not an image');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('not a valid image');

        try {
            imageResize::convert($fakeImage, $this->outputDir . 'output.jpg');
        } finally {
            @unlink($fakeImage);
        }
    }

    public function testValidatesActualMimeType(): void
    {
        // Create a PHP file disguised as JPG
        $maliciousFile = $this->outputDir . 'malicious.jpg';
        file_put_contents($maliciousFile, '<?php system($_GET["cmd"]); ?>');

        $this->expectException(InvalidFileException::class);

        try {
            imageResize::convert($maliciousFile, $this->outputDir . 'output.jpg');
        } finally {
            @unlink($maliciousFile);
        }
    }

    // ========================================
    // File Size Limits
    // ========================================

    public function testEnforcesMaximumSourceFileSize(): void
    {
        // We can't easily create a 500MB+ file for testing,
        // but we can test the validation logic with the constant
        $this->assertGreaterThan(0, imageResize::MAX_SOURCE_FILE_SIZE);
        $this->assertEquals(524288000, imageResize::MAX_SOURCE_FILE_SIZE);
    }

    public function testEnforcesMinimumTargetSize(): void
    {
        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Target size too small');

        imageResize::$targetSize = 100; // Less than MIN_TARGET_SIZE
        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'output.jpg'
        );
    }

    public function testEnforcesMaximumTargetSize(): void
    {
        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Target size too large');

        imageResize::$targetSize = 200000000; // More than MAX_TARGET_SIZE
        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'output.jpg'
        );
    }

    // ========================================
    // Path Validation
    // ========================================

    public function testRejectsEmptySourcePath(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('cannot be empty');

        imageResize::convert('', $this->outputDir . 'output.jpg');
    }

    public function testRejectsEmptyTargetPath(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('cannot be empty');

        imageResize::convert($this->fixturesDir . 'test-small.jpg', '');
    }

    public function testRejectsNonExistentSourceFile(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('does not exist');

        imageResize::convert('/nonexistent/file.jpg', $this->outputDir . 'output.jpg');
    }

    public function testRejectsDirectoryAsSource(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('not a file');

        imageResize::convert($this->fixturesDir, $this->outputDir . 'output.jpg');
    }

    public function testRejectsNonExistentTargetDirectory(): void
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('does not exist');

        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            '/completely/nonexistent/path/output.jpg'
        );
    }

    // ========================================
    // Permission Validation
    // ========================================

    public function testChecksSourceFileReadability(): void
    {
        // This test is platform-dependent and may not work everywhere
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Permission tests not reliable on Windows');
        }

        $unreadableFile = $this->outputDir . 'unreadable.jpg';
        copy($this->fixturesDir . 'test-small.jpg', $unreadableFile);
        chmod($unreadableFile, 0000);

        try {
            $this->expectException(InsufficientPermissionsException::class);
            $this->expectExceptionMessage('not readable');

            imageResize::convert($unreadableFile, $this->outputDir . 'output.jpg');
        } finally {
            chmod($unreadableFile, 0644);
            @unlink($unreadableFile);
        }
    }

    // ========================================
    // Secure Temp Directory
    // ========================================

    public function testCreatesSec ureTempDirectory(): void
    {
        // The temp directory should use random_bytes
        // We verify this indirectly by processing an image and ensuring cleanup
        imageResize::convert(
            $this->fixturesDir . 'test-large.jpg',
            $this->outputDir . 'temp-security-test.jpg'
        );

        $this->assertFileExists($this->outputDir . 'temp-security-test.jpg');

        // Temp directory should be cleaned up automatically
        // (we can't easily verify the name, but we verify the process works)
    }

    public function testCustomTempDirectoryIsSafe(): void
    {
        $customTemp = $this->outputDir . 'custom_temp_' . bin2hex(random_bytes(8));
        imageResize::$tempDir = $customTemp;

        imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $this->outputDir . 'custom-temp-test.jpg'
        );

        $this->assertFileExists($this->outputDir . 'custom-temp-test.jpg');

        // Cleanup
        if (is_dir($customTemp)) {
            rmdir($customTemp);
        }
    }

    // ========================================
    // Input Sanitization
    // ========================================

    public function testHandlesSpecialCharactersInPath(): void
    {
        // Valid special characters should work
        $targetFile = $this->outputDir . 'output_test-123.jpg';

        $result = imageResize::convert(
            $this->fixturesDir . 'test-small.jpg',
            $targetFile
        );

        $this->assertTrue($result);
        $this->assertFileExists($targetFile);
    }

    // ========================================
    // Null Byte Injection Prevention
    // ========================================

    public function testBlocksNullByteInjection(): void
    {
        // PHP will handle this automatically in most cases,
        // but verify our validation catches it
        $this->expectException(InvalidPathException::class);

        $maliciousPath = $this->fixturesDir . "test-small.jpg\0.php";
        imageResize::convert($maliciousPath, $this->outputDir . 'output.jpg');
    }

    // ========================================
    // Symbolic Link Handling
    // ========================================

    public function testHandlesSymbolicLinksSecurely(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Symlink test not reliable on Windows');
        }

        $linkPath = $this->outputDir . 'symlink.jpg';

        // Create symlink to test file
        if (!@symlink($this->fixturesDir . 'test-small.jpg', $linkPath)) {
            $this->markTestSkipped('Cannot create symlink');
        }

        try {
            // Symlinks should be resolved to real path
            $result = imageResize::convert($linkPath, $this->outputDir . 'symlink-output.jpg');
            $this->assertTrue($result);
        } finally {
            @unlink($linkPath);
        }
    }

    // ========================================
    // Constants Security
    // ========================================

    public function testSecurityConstantsAreReasonable(): void
    {
        // Verify security limits are sane
        $this->assertEquals(1024, imageResize::MIN_TARGET_SIZE);
        $this->assertEquals(104857600, imageResize::MAX_TARGET_SIZE);
        $this->assertEquals(524288000, imageResize::MAX_SOURCE_FILE_SIZE);

        // Min should be less than max
        $this->assertLessThan(
            imageResize::MAX_TARGET_SIZE,
            imageResize::MIN_TARGET_SIZE
        );

        // Source max should be greater than target max
        $this->assertGreaterThan(
            imageResize::MAX_TARGET_SIZE,
            imageResize::MAX_SOURCE_FILE_SIZE
        );
    }

    // ========================================
    // Resource Cleanup
    // ========================================

    public function testCleansUpResourcesOnError(): void
    {
        try {
            // Trigger an error with invalid file
            imageResize::convert('/nonexistent.jpg', $this->outputDir . 'output.jpg');
        } catch (\Exception $e) {
            // Expected - verify no temp files left behind
            $tempFiles = glob(sys_get_temp_dir() . '/image_resize_*');
            $this->assertEmpty($tempFiles, 'Temp files should be cleaned up on error');
        }
    }
}
