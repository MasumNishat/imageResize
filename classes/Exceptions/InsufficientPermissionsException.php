<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Exceptions;

/**
 * Exception thrown when file permissions prevent operation
 *
 * This exception is thrown when:
 * - Source file is not readable
 * - Target directory is not writable
 * - Cannot create temp directory
 * - Cannot delete temp files
 *
 * @package MasumNishat\imageResize\Exceptions
 */
class InsufficientPermissionsException extends ImageResizeException
{
}
