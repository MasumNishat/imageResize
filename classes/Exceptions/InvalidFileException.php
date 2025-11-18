<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Exceptions;

/**
 * Exception thrown when a file is invalid or doesn't exist
 *
 * This exception is thrown when:
 * - Source file doesn't exist
 * - File is not a valid image
 * - File is corrupted
 * - File size exceeds limits
 *
 * @package MasumNishat\imageResize\Exceptions
 */
class InvalidFileException extends ImageResizeException
{
}
