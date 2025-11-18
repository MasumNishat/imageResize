<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Exceptions;

/**
 * Exception thrown when there's not enough disk space
 *
 * This exception is thrown when:
 * - Insufficient disk space for temporary files
 * - Insufficient disk space for output file
 * - Disk space check fails
 *
 * @package MasumNishat\imageResize\Exceptions
 */
class InsufficientDiskSpaceException extends ImageResizeException
{
}
