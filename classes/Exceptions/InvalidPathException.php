<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Exceptions;

/**
 * Exception thrown when a file path is invalid or dangerous
 *
 * This exception is thrown when:
 * - Path contains directory traversal attempts (../)
 * - Path is outside allowed directories
 * - Path contains invalid characters
 * - Path is empty or malformed
 *
 * @package MasumNishat\imageResize\Exceptions
 */
class InvalidPathException extends ImageResizeException
{
}
