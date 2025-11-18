<?php

declare(strict_types=1);

namespace MasumNishat\imageResize;

/**
 * Result object containing information about a resize operation
 *
 * This class holds metadata about the resize operation including
 * file sizes, dimensions, compression ratio, and success status.
 *
 * @package MasumNishat\imageResize
 */
class ResizeResult
{
    /** @var string Path to the output file */
    public string $outputPath;

    /** @var int Original file size in bytes */
    public int $originalSize;

    /** @var int Final file size in bytes */
    public int $finalSize;

    /** @var array{0: int, 1: int} Original dimensions [width, height] */
    public array $originalDimensions;

    /** @var array{0: int, 1: int} Final dimensions [width, height] */
    public array $finalDimensions;

    /** @var float Compression ratio (0.0 to 1.0) */
    public float $compressionRatio;

    /** @var string MIME type of the image */
    public string $mimeType;

    /** @var bool Whether compression was needed */
    public bool $wasCompressed;

    /** @var float Processing time in seconds */
    public float $processingTime;

    /**
     * Create a new ResizeResult instance
     *
     * @param string $outputPath Path to output file
     * @param int $originalSize Original file size in bytes
     * @param int $finalSize Final file size in bytes
     * @param array{0: int, 1: int} $originalDimensions Original [width, height]
     * @param array{0: int, 1: int} $finalDimensions Final [width, height]
     * @param string $mimeType MIME type of the image
     * @param bool $wasCompressed Whether compression was applied
     * @param float $processingTime Processing time in seconds
     */
    public function __construct(
        string $outputPath,
        int $originalSize,
        int $finalSize,
        array $originalDimensions,
        array $finalDimensions,
        string $mimeType,
        bool $wasCompressed,
        float $processingTime
    ) {
        $this->outputPath = $outputPath;
        $this->originalSize = $originalSize;
        $this->finalSize = $finalSize;
        $this->originalDimensions = $originalDimensions;
        $this->finalDimensions = $finalDimensions;
        $this->mimeType = $mimeType;
        $this->wasCompressed = $wasCompressed;
        $this->processingTime = $processingTime;

        // Calculate compression ratio
        $this->compressionRatio = $originalSize > 0
            ? ($finalSize / $originalSize)
            : 1.0;
    }

    /**
     * Get compression percentage
     *
     * @return float Percentage of compression (0-100)
     */
    public function getCompressionPercentage(): float
    {
        return (1.0 - $this->compressionRatio) * 100;
    }

    /**
     * Get size reduction in bytes
     *
     * @return int Bytes saved
     */
    public function getSizeReduction(): int
    {
        return $this->originalSize - $this->finalSize;
    }

    /**
     * Get original dimensions as string
     *
     * @return string Format: "1920x1080"
     */
    public function getOriginalDimensionsString(): string
    {
        return $this->originalDimensions[0] . 'x' . $this->originalDimensions[1];
    }

    /**
     * Get final dimensions as string
     *
     * @return string Format: "1280x720"
     */
    public function getFinalDimensionsString(): string
    {
        return $this->finalDimensions[0] . 'x' . $this->finalDimensions[1];
    }

    /**
     * Convert to array representation
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'output_path' => $this->outputPath,
            'original_size' => $this->originalSize,
            'final_size' => $this->finalSize,
            'original_dimensions' => $this->originalDimensions,
            'final_dimensions' => $this->finalDimensions,
            'compression_ratio' => $this->compressionRatio,
            'compression_percentage' => $this->getCompressionPercentage(),
            'size_reduction' => $this->getSizeReduction(),
            'mime_type' => $this->mimeType,
            'was_compressed' => $this->wasCompressed,
            'processing_time' => $this->processingTime,
        ];
    }

    /**
     * Get a human-readable summary
     *
     * @return string
     */
    public function getSummary(): string
    {
        $summary = sprintf(
            "Resized: %s → %s (%s → %s, %.1f%% reduction, %.2fs)",
            $this->getOriginalDimensionsString(),
            $this->getFinalDimensionsString(),
            $this->formatBytes($this->originalSize),
            $this->formatBytes($this->finalSize),
            $this->getCompressionPercentage(),
            $this->processingTime
        );

        return $summary;
    }

    /**
     * Format bytes to human-readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
