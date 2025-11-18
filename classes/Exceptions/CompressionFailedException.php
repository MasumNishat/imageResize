<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Exceptions;

/**
 * Exception thrown when image compression fails
 *
 * This exception is thrown when:
 * - Cannot achieve target file size
 * - Image processing fails
 * - GD library functions fail
 * - Output file cannot be created
 *
 * @package MasumNishat\imageResize\Exceptions
 */
class CompressionFailedException extends ImageResizeException
{
}
