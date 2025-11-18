<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Exceptions;

/**
 * Exception thrown when an image format is not supported
 *
 * This exception is thrown when:
 * - Image MIME type is not in the allowed list
 * - Image type cannot be determined
 * - GD library doesn't support the format
 *
 * Supported formats: JPEG, PNG, GIF
 *
 * @package MasumNishat\imageResize\Exceptions
 */
class UnsupportedFormatException extends ImageResizeException
{
}
