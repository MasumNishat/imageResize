<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Tests\Unit;

use PHPUnit\Framework\TestCase;
use MasumNishat\imageResize\Exceptions\ImageResizeException;
use MasumNishat\imageResize\Exceptions\InvalidFileException;
use MasumNishat\imageResize\Exceptions\InvalidPathException;
use MasumNishat\imageResize\Exceptions\UnsupportedFormatException;
use MasumNishat\imageResize\Exceptions\CompressionFailedException;
use MasumNishat\imageResize\Exceptions\InsufficientPermissionsException;
use MasumNishat\imageResize\Exceptions\InsufficientDiskSpaceException;

/**
 * Test all custom exception classes
 */
class ExceptionsTest extends TestCase
{
    public function testImageResizeExceptionCanBeThrown(): void
    {
        $this->expectException(ImageResizeException::class);
        $this->expectExceptionMessage('Test exception');

        throw new ImageResizeException('Test exception');
    }

    public function testInvalidFileExceptionExtendsBaseException(): void
    {
        $exception = new InvalidFileException('File error');

        $this->assertInstanceOf(ImageResizeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testInvalidPathExceptionExtendsBaseException(): void
    {
        $exception = new InvalidPathException('Path error');

        $this->assertInstanceOf(ImageResizeException::class, $exception);
        $this->assertEquals('Path error', $exception->getMessage());
    }

    public function testUnsupportedFormatExceptionExtendsBaseException(): void
    {
        $exception = new UnsupportedFormatException('Format error');

        $this->assertInstanceOf(ImageResizeException::class, $exception);
    }

    public function testCompressionFailedExceptionExtendsBaseException(): void
    {
        $exception = new CompressionFailedException('Compression error');

        $this->assertInstanceOf(ImageResizeException::class, $exception);
    }

    public function testInsufficientPermissionsExceptionExtendsBaseException(): void
    {
        $exception = new InsufficientPermissionsException('Permission error');

        $this->assertInstanceOf(ImageResizeException::class, $exception);
    }

    public function testInsufficientDiskSpaceExceptionExtendsBaseException(): void
    {
        $exception = new InsufficientDiskSpaceException('Disk space error');

        $this->assertInstanceOf(ImageResizeException::class, $exception);
    }

    public function testExceptionMessagesArePreserved(): void
    {
        $message = 'Custom error message';
        $exception = new InvalidFileException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionCodesAreSupported(): void
    {
        $exception = new InvalidFileException('Error', 123);

        $this->assertEquals(123, $exception->getCode());
    }

    public function testExceptionPreviousIsSupported(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new ImageResizeException('New exception', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
