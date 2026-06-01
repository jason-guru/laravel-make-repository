<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Exceptions;

use Exception;
use Throwable;

class GeneralException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function report(): void
    {
        //
    }
}
