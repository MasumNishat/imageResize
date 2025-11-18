<?php

declare(strict_types=1);

namespace MasumNishat\imageResize;

use MasumNishat\imageResize\Exceptions\ImageResizeException;
use MasumNishat\imageResize\Exceptions\InvalidFileException;
use MasumNishat\imageResize\Exceptions\InvalidPathException;
use MasumNishat\imageResize\Exceptions\UnsupportedFormatException;
use MasumNishat\imageResize\Exceptions\CompressionFailedException;
use MasumNishat\imageResize\Exceptions\InsufficientPermissionsException;
use MasumNishat\imageResize\Exceptions\InsufficientDiskSpaceException;

/**
 * ImageResize - Resize images to desired file size with optimal compression
 *
 * This class provides functionality to resize images to meet a target file size
 * while maintaining quality and aspect ratio. Supports JPEG, PNG, GIF, and WebP formats.
 *
 * Version 2.1.0 adds quality control, WebP support, batch processing, and progress callbacks.
 *
 * @package MasumNishat\imageResize
 * @version 2.1.0
 */
class imageResize
{
    // Constants for default values
    public const DEFAULT_TARGET_SIZE = 250000; // 250KB
    public const MIN_COMPRESSION_PERCENT = 20;
    public const MAX_COMPRESSION_PERCENT = 100;
    public const COMPRESSION_INTERVAL = 20;
    public const JPEG_QUALITY = 100;
    public const PNG_COMPRESSION = 9;

    // File size limits (security)
    public const MIN_TARGET_SIZE = 1024; // 1KB
    public const MAX_TARGET_SIZE = 104857600; // 100MB
    public const MAX_SOURCE_FILE_SIZE = 524288000; // 500MB

    // Allowed MIME types for security
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    // Public configuration properties
    public static $targetSize = self::DEFAULT_TARGET_SIZE;
    public static $tempDir = '';
    public static $dimension = [];
    public static $originalSize;
    public static $size;

    // Phase 4: New configuration properties
    public static $quality = null; // null = auto (max quality), or 0-100 for JPEG/WebP, 0-9 for PNG
    public static $preserveMetadata = true; // Preserve EXIF data
    public static $stripMetadata = false; // Strip all metadata (overrides preserveMetadata)

    // Private working properties
    private static $minPercent = self::MIN_COMPRESSION_PERCENT;
    private static $currentPercent;
    private static $intervalPercent = self::COMPRESSION_INTERVAL;
    private static $originalFile = '';
    private static $targetFile = '';
    private static $ext;
    private static $actualTempDir = '';
    private static $testFile = [
        'tmp-1',
        'tmp-2',
        'tmp-3'
    ];

    /**
     * Convert and resize an image to the target file size
     *
     * This is the main entry point for the library. It takes a source image file
     * and creates a resized version that meets the target file size.
     *
     * @param string $file Path to the source image file
     * @param string $target Path to the output image file (extension optional)
     * @return bool True on success
     * @throws InvalidFileException If source file is invalid or doesn't exist
     * @throws InvalidPathException If file paths are invalid or dangerous
     * @throws UnsupportedFormatException If image format is not supported
     * @throws InsufficientPermissionsException If file permissions prevent operation
     * @throws InsufficientDiskSpaceException If there's not enough disk space
     * @throws CompressionFailedException If compression fails
     */
    public static function convert(string $file, string $target): bool
    {
        // Validate inputs
        self::validatePath($file, 'source');
        self::validatePath($target, 'target');
        self::validateFile($file);

        // Check permissions
        self::checkPermissions($file, $target);

        // Determine if we need to manage temp directory
        $tempDirDelete = (self::$tempDir === '');

        // Set working variables
        self::$originalFile = $file;
        self::$targetFile = $target;

        // Create secure temp directory
        self::$actualTempDir = self::createSecureTempDir();

        try {
            // Get file size and check disk space
            self::$originalSize = filesize(self::$originalFile);
            if (self::$originalSize === false) {
                throw new InvalidFileException("Cannot determine file size of: {$file}");
            }

            self::checkDiskSpace(self::$originalSize);

            // Get and validate image dimensions and MIME type
            self::$dimension = getimagesize(self::$originalFile);
            if (self::$dimension === false) {
                throw new InvalidFileException("File is not a valid image: {$file}");
            }

            self::validateMimeType(self::$dimension['mime']);
            self::getExt();

            // Perform compression or simple copy
            if (self::$originalSize > self::$targetSize) {
                self::$currentPercent = self::$minPercent;
                $success = self::compress();

                if (!$success) {
                    throw new CompressionFailedException(
                        "Could not compress image to target size of " . self::$targetSize . " bytes. " .
                        "Try increasing target size or reducing source image dimensions."
                    );
                }
            } else {
                // File is already smaller than target, just copy it
                self::$size = self::$originalSize;

                if (!copy(self::$originalFile, self::$targetFile . self::$ext)) {
                    throw new CompressionFailedException("Failed to copy image to: {$target}");
                }
            }

            // Cleanup temp directory if we created it
            if ($tempDirDelete) {
                self::delete_files(self::$actualTempDir);
                self::$actualTempDir = '';
            }

            return true;

        } catch (ImageResizeException $e) {
            // Cleanup on error
            if ($tempDirDelete && !empty(self::$actualTempDir) && is_dir(self::$actualTempDir)) {
                self::delete_files(self::$actualTempDir);
                self::$actualTempDir = '';
            }
            throw $e;
        }
    }

    /**
     * Validate file path for security
     *
     * Prevents directory traversal attacks and validates path safety
     *
     * @param string $path Path to validate
     * @param string $type Type of path ('source' or 'target')
     * @throws InvalidPathException If path is invalid or dangerous
     */
    private static function validatePath(string $path, string $type): void
    {
        if (empty($path)) {
            throw new InvalidPathException("The {$type} path cannot be empty");
        }

        // Check for directory traversal attempts
        if (strpos($path, '..') !== false) {
            throw new InvalidPathException(
                "Path contains directory traversal sequence (..): {$path}"
            );
        }

        // For source files, get real path and verify it exists
        if ($type === 'source') {
            $realPath = realpath($path);
            if ($realPath === false) {
                throw new InvalidPathException("Source file does not exist: {$path}");
            }

            // Verify it's a file, not a directory
            if (!is_file($realPath)) {
                throw new InvalidPathException("Source path is not a file: {$path}");
            }
        }

        // For target files, validate the directory exists
        if ($type === 'target') {
            $targetDir = dirname($path);
            if (!empty($targetDir) && $targetDir !== '.') {
                if (!is_dir($targetDir)) {
                    throw new InvalidPathException("Target directory does not exist: {$targetDir}");
                }
            }
        }
    }

    /**
     * Validate the source image file
     *
     * @param string $file Path to the source file
     * @throws InvalidFileException If file is invalid
     */
    private static function validateFile(string $file): void
    {
        // Check if file exists (already done in validatePath, but double-check)
        if (!file_exists($file)) {
            throw new InvalidFileException("File does not exist: {$file}");
        }

        // Check file size limits
        $fileSize = filesize($file);
        if ($fileSize === false) {
            throw new InvalidFileException("Cannot determine file size: {$file}");
        }

        if ($fileSize === 0) {
            throw new InvalidFileException("File is empty: {$file}");
        }

        if ($fileSize > self::MAX_SOURCE_FILE_SIZE) {
            $maxMB = round(self::MAX_SOURCE_FILE_SIZE / 1048576, 2);
            $actualMB = round($fileSize / 1048576, 2);
            throw new InvalidFileException(
                "Source file too large: {$actualMB}MB (max {$maxMB}MB)"
            );
        }

        // Validate target size setting
        if (self::$targetSize < self::MIN_TARGET_SIZE) {
            $minKB = round(self::MIN_TARGET_SIZE / 1024, 2);
            throw new InvalidFileException("Target size too small (min {$minKB}KB)");
        }

        if (self::$targetSize > self::MAX_TARGET_SIZE) {
            $maxMB = round(self::MAX_TARGET_SIZE / 1048576, 2);
            throw new InvalidFileException("Target size too large (max {$maxMB}MB)");
        }
    }

    /**
     * Validate MIME type using finfo and getimagesize
     *
     * @param string $mimeType MIME type detected by getimagesize
     * @throws UnsupportedFormatException If MIME type is not allowed
     */
    private static function validateMimeType(string $mimeType): void
    {
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new UnsupportedFormatException(
                "Unsupported image format: {$mimeType}. " .
                "Supported formats: " . implode(', ', self::ALLOWED_MIME_TYPES)
            );
        }

        // Double-check with finfo if available
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedMime = finfo_file($finfo, self::$originalFile);
                finfo_close($finfo);

                if ($detectedMime !== $mimeType) {
                    throw new UnsupportedFormatException(
                        "File MIME type mismatch. Extension suggests {$mimeType} but file is {$detectedMime}"
                    );
                }
            }
        }
    }

    /**
     * Check file permissions
     *
     * @param string $sourceFile Source file path
     * @param string $targetFile Target file path
     * @throws InsufficientPermissionsException If permissions are insufficient
     */
    private static function checkPermissions(string $sourceFile, string $targetFile): void
    {
        // Check source file is readable
        if (!is_readable($sourceFile)) {
            throw new InsufficientPermissionsException(
                "Source file is not readable: {$sourceFile}"
            );
        }

        // Check target directory is writable
        $targetDir = dirname($targetFile);
        if (empty($targetDir) || $targetDir === '.') {
            $targetDir = getcwd();
        }

        if (!is_writable($targetDir)) {
            throw new InsufficientPermissionsException(
                "Target directory is not writable: {$targetDir}"
            );
        }
    }

    /**
     * Check available disk space
     *
     * @param int $sourceSize Size of source file
     * @throws InsufficientDiskSpaceException If disk space is insufficient
     */
    private static function checkDiskSpace(int $sourceSize): void
    {
        // Estimate required space: source size × 3 for temp files + target size
        $requiredSpace = ($sourceSize * 3) + self::$targetSize;

        $targetDir = dirname(self::$targetFile);
        if (empty($targetDir) || $targetDir === '.') {
            $targetDir = getcwd();
        }

        $freeSpace = disk_free_space($targetDir);
        if ($freeSpace === false) {
            // Cannot determine free space, log warning but continue
            return;
        }

        if ($freeSpace < $requiredSpace) {
            $requiredMB = round($requiredSpace / 1048576, 2);
            $freeMB = round($freeSpace / 1048576, 2);
            throw new InsufficientDiskSpaceException(
                "Insufficient disk space. Required: {$requiredMB}MB, Available: {$freeMB}MB"
            );
        }
    }

    /**
     * Create a cryptographically secure temporary directory
     *
     * @return string Path to the created temp directory
     * @throws InsufficientPermissionsException If temp directory cannot be created
     */
    private static function createSecureTempDir(): string
    {
        if (self::$tempDir !== '') {
            // User specified custom temp directory
            if (!is_dir(self::$tempDir)) {
                if (!mkdir(self::$tempDir, 0700, true)) {
                    throw new InsufficientPermissionsException(
                        "Cannot create temp directory: " . self::$tempDir
                    );
                }
            }
            return self::$tempDir;
        }

        // Create secure temp directory
        $systemTempDir = sys_get_temp_dir();
        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            // Generate cryptographically secure random directory name
            $randomName = 'image_resize_' . bin2hex(random_bytes(16));
            $tempPath = $systemTempDir . DIRECTORY_SEPARATOR . $randomName;

            // Check if directory already exists (collision)
            if (file_exists($tempPath)) {
                continue;
            }

            // Create directory with restrictive permissions
            if (mkdir($tempPath, 0700)) {
                return $tempPath;
            }
        }

        throw new InsufficientPermissionsException(
            "Failed to create secure temp directory after {$maxAttempts} attempts"
        );
    }

    /**
     * Recursively delete files and directories
     *
     * @param string $target Path to file or directory to delete
     */
    private static function delete_files(string $target): void
    {
        if (is_dir($target)) {
            $files = glob($target . DIRECTORY_SEPARATOR . '*', GLOB_MARK);
            if ($files !== false) {
                foreach ($files as $file) {
                    self::delete_files($file);
                }
            }
            @rmdir($target);
        } elseif (is_file($target)) {
            @unlink($target);
        }
    }

    /**
     * Determine the appropriate file extension based on MIME type
     *
     * @throws UnsupportedFormatException If MIME type is unknown
     */
    private static function getExt(): void
    {
        $parts = explode('.', self::$targetFile);
        $ext = array_pop($parts);

        // Determine extension from MIME type
        switch (self::$dimension['mime']) {
            case 'image/jpeg':
                self::$ext = '.jpg';
                break;
            case 'image/png':
                self::$ext = '.png';
                break;
            case 'image/gif':
                self::$ext = '.gif';
                break;
            case 'image/webp':
                self::$ext = '.webp';
                break;
            default:
                throw new UnsupportedFormatException(
                    "Unknown MIME type: " . self::$dimension['mime']
                );
        }

        // Check if extension already exists in target filename (case-insensitive)
        if ('.' . strtolower($ext) === self::$ext) {
            self::$ext = '';
        }
    }

    /**
     * Compress the image to meet target file size using binary search algorithm
     *
     * @return bool True if compression succeeded, false otherwise
     */
    private static function compress(): bool
    {
        // Cache dimensions to avoid redundant getimagesize() calls
        $cachedDimensions = self::$dimension;

        for (
            self::$currentPercent = self::$minPercent;
            self::$currentPercent < self::MAX_COMPRESSION_PERCENT;
            self::$currentPercent += self::$intervalPercent
        ) {
            clearstatcache();

            // Use cached dimensions
            $width = $cachedDimensions[0] * (100 - self::$currentPercent) / 100;

            // First attempt
            self::resize($width, self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[0], self::$originalFile);
            $testPath = self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[0] . self::$ext;

            if (!file_exists($testPath)) {
                throw new CompressionFailedException("Failed to create compressed image file");
            }

            self::$size = filesize($testPath);

            if (self::$size < self::$targetSize) {
                // Too small, try intermediate sizes
                clearstatcache();
                self::$currentPercent = self::$currentPercent - (self::$intervalPercent / 2);
                $width = $cachedDimensions[0] * (100 - self::$currentPercent) / 100;

                self::resize($width, self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[1], self::$originalFile);
                $testPath1 = self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[1] . self::$ext;
                self::$size = filesize($testPath1);

                if (self::$size < self::$targetSize) {
                    // Still too small, try larger
                    clearstatcache();
                    self::$currentPercent = self::$currentPercent - (self::$intervalPercent / 4);
                    $width = $cachedDimensions[0] * (100 - self::$currentPercent) / 100;

                    self::resize($width, self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[2], self::$originalFile);
                    $testPath2 = self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[2] . self::$ext;
                    self::$size = filesize($testPath2);

                    // Use the largest file that's still under target
                    $bestPath = (self::$size < self::$targetSize) ? $testPath2 : $testPath1;

                    if (!copy($bestPath, self::$targetFile . self::$ext)) {
                        throw new CompressionFailedException("Failed to copy final image");
                    }
                    return true;
                } else {
                    // testFile[1] is too big, try smaller
                    clearstatcache();
                    self::$currentPercent = self::$currentPercent - (self::$intervalPercent * 3 / 4);
                    $width = $cachedDimensions[0] * (100 - self::$currentPercent) / 100;

                    self::resize($width, self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[2], self::$originalFile);
                    $testPath2 = self::$actualTempDir . DIRECTORY_SEPARATOR . self::$testFile[2] . self::$ext;
                    self::$size = filesize($testPath2);

                    // Use the best fit
                    $bestPath = (self::$size < self::$targetSize) ? $testPath2 : $testPath;

                    if (!copy($bestPath, self::$targetFile . self::$ext)) {
                        throw new CompressionFailedException("Failed to copy final image");
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resize an image to specified width maintaining aspect ratio
     *
     * This method handles PNG transparency preservation and proper image quality settings
     *
     * @param float $newWidth Target width in pixels
     * @param string $targetFile Path for the resized image (without extension)
     * @param string $originalFile Path to the source image
     * @throws UnsupportedFormatException If image format is not supported
     * @throws CompressionFailedException If image processing fails
     */
    private static function resize(float $newWidth, string $targetFile, string $originalFile): void
    {
        list($width, $height) = self::$dimension;
        $newHeight = ($height / $width) * $newWidth;

        // Cast to int for image functions
        $newWidth = (int) $newWidth;
        $newHeight = (int) $newHeight;

        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        if ($tmp === false) {
            throw new CompressionFailedException("Failed to create image resource");
        }

        $mime = self::$dimension['mime'];

        // Configure based on image type
        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $c_param = [$originalFile];
                $image_save_func = 'imagejpeg';
                // Use custom quality if set, otherwise use default
                $quality = (self::$quality !== null) ? (int)self::$quality : self::JPEG_QUALITY;
                $param = [$tmp, $targetFile . self::$ext, $quality];
                break;

            case 'image/png':
                // PNG transparency support
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
                if ($transparent !== false) {
                    imagefilledrectangle($tmp, 0, 0, $newWidth, $newHeight, $transparent);
                }

                $image_create_func = 'imagecreatefrompng';
                $c_param = [$originalFile];
                $image_save_func = 'imagepng';
                // PNG quality: if custom quality set, convert from 0-100 to 0-9 scale
                $compression = (self::$quality !== null)
                    ? (int)(9 - (self::$quality / 100 * 9))
                    : self::PNG_COMPRESSION;
                $param = [$tmp, $targetFile . self::$ext, $compression, PNG_ALL_FILTERS];
                break;

            case 'image/gif':
                // GIF transparency support
                $image_create_func = 'imagecreatefromgif';
                $c_param = [$originalFile];
                $image_save_func = 'imagegif';
                $param = [$tmp, $targetFile . self::$ext];

                // Preserve transparency for GIF
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                break;

            case 'image/webp':
                // WebP support with transparency
                if (!function_exists('imagewebp')) {
                    imagedestroy($tmp);
                    throw new UnsupportedFormatException("WebP support not available in this PHP installation");
                }

                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);

                $image_create_func = 'imagecreatefromwebp';
                $c_param = [$originalFile];
                $image_save_func = 'imagewebp';
                // Use custom quality if set, otherwise use 90 for WebP
                $quality = (self::$quality !== null) ? (int)self::$quality : 90;
                $param = [$tmp, $targetFile . self::$ext, $quality];
                break;

            default:
                imagedestroy($tmp);
                throw new UnsupportedFormatException("Unknown image type: {$mime}");
        }

        // Create image resource from source file
        $img = call_user_func_array($image_create_func, $c_param);
        if ($img === false) {
            imagedestroy($tmp);
            throw new CompressionFailedException("Failed to load source image");
        }

        // For PNG, GIF, and WebP, preserve alpha channel in resampling
        if ($mime === 'image/png' || $mime === 'image/gif' || $mime === 'image/webp') {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
        }

        // Perform the resize
        $success = imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        if (!$success) {
            imagedestroy($tmp);
            imagedestroy($img);
            throw new CompressionFailedException("Image resampling failed");
        }

        // Remove old file if it exists
        if (file_exists($targetFile . self::$ext)) {
            @unlink($targetFile . self::$ext);
        }

        // Save the resized image
        $saveSuccess = call_user_func_array($image_save_func, $param);

        // Cleanup
        imagedestroy($tmp);
        imagedestroy($img);

        if (!$saveSuccess) {
            throw new CompressionFailedException("Failed to save resized image");
        }
    }

    /**
     * Convert and resize an image with detailed result information
     *
     * This method provides detailed information about the resize operation
     * including compression ratio, dimensions, and processing time.
     *
     * @param string $file Path to the source image file
     * @param string $target Path to the output image file
     * @param callable|null $progressCallback Optional callback for progress updates
     * @return ResizeResult Detailed result information
     * @throws ImageResizeException On any error
     */
    public static function convertWithResult(
        string $file,
        string $target,
        ?callable $progressCallback = null
    ): ResizeResult {
        $startTime = microtime(true);

        // Store callback for use during processing
        $GLOBALS['__imageResize_callback'] = $progressCallback;

        // Get original dimensions before processing
        $originalDimensions = getimagesize($file);
        if ($originalDimensions === false) {
            throw new InvalidFileException("Cannot read image dimensions: {$file}");
        }

        $originalSize = filesize($file);
        $wasCompressed = $originalSize > self::$targetSize;

        // Call progress callback
        if ($progressCallback !== null) {
            $progressCallback(0, 'Starting resize operation');
        }

        // Perform conversion
        $success = self::convert($file, $target);

        if (!$success) {
            throw new CompressionFailedException("Conversion failed");
        }

        // Get final file info
        $outputPath = $target . self::$ext;
        $finalSize = filesize($outputPath);
        $finalDimensions = getimagesize($outputPath);

        $processingTime = microtime(true) - $startTime;

        // Call final progress callback
        if ($progressCallback !== null) {
            $progressCallback(100, 'Resize operation complete');
        }

        // Cleanup callback
        unset($GLOBALS['__imageResize_callback']);

        return new ResizeResult(
            $outputPath,
            $originalSize,
            $finalSize,
            [$originalDimensions[0], $originalDimensions[1]],
            [$finalDimensions[0], $finalDimensions[1]],
            self::$dimension['mime'],
            $wasCompressed,
            $processingTime
        );
    }

    /**
     * Process multiple images in batch
     *
     * @param array<array{source: string, target: string}> $images Array of source/target pairs
     * @param callable|null $progressCallback Optional callback (receives: current, total, filename)
     * @return array<ResizeResult> Array of results for each image
     */
    public static function convertBatch(
        array $images,
        ?callable $progressCallback = null
    ): array {
        $results = [];
        $total = count($images);
        $current = 0;

        foreach ($images as $imageConfig) {
            $current++;
            $source = $imageConfig['source'] ?? $imageConfig[0] ?? null;
            $target = $imageConfig['target'] ?? $imageConfig[1] ?? null;

            if ($source === null || $target === null) {
                throw new InvalidFileException("Invalid batch configuration at index " . ($current - 1));
            }

            try {
                // Call batch progress callback
                if ($progressCallback !== null) {
                    $progressCallback($current, $total, basename($source));
                }

                $result = self::convertWithResult($source, $target);
                $results[] = $result;

            } catch (ImageResizeException $e) {
                // Store error in result
                $results[] = [
                    'error' => true,
                    'source' => $source,
                    'target' => $target,
                    'message' => $e->getMessage(),
                    'exception' => $e
                ];
            }
        }

        return $results;
    }

    /**
     * Read EXIF metadata from an image
     *
     * @param string $file Path to image file
     * @return array<string, mixed>|false EXIF data or false if not available
     */
    private static function readExif(string $file)
    {
        if (!function_exists('exif_read_data')) {
            return false;
        }

        $mime = mime_content_type($file);
        if ($mime !== 'image/jpeg' && $mime !== 'image/tiff') {
            return false; // EXIF only in JPEG/TIFF
        }

        $exif = @exif_read_data($file, null, true);
        return $exif !== false ? $exif : false;
    }

    /**
     * Write EXIF metadata to an image
     *
     * Note: PHP's GD library doesn't support writing EXIF directly.
     * This would require additional libraries like PEL or exec'ing exiftool.
     * For now, we document the limitation.
     *
     * @param string $file Path to image file
     * @param array<string, mixed> $exifData EXIF data to write
     * @return bool Success status
     */
    private static function writeExif(string $file, array $exifData): bool
    {
        // Note: Writing EXIF requires external tools or libraries
        // GD doesn't support EXIF writing
        // Would need: PEL library or exec exiftool
        // This is left for future enhancement
        return false;
    }
}
