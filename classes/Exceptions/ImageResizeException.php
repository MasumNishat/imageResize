<?php

declare(strict_types=1);

namespace MasumNishat\imageResize\Exceptions;

use Exception;

/**
 * Base exception class for all ImageResize exceptions
 *
 * This is the parent class for all exceptions thrown by the ImageResize library.
 * Catching this exception will catch all library-specific errors.
 *
 * @package MasumNishat\imageResize\Exceptions
 */
class ImageResizeException extends Exception
{
}
