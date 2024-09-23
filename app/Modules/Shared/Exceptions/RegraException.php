<?php

namespace App\Modules\Shared\Exceptions;

use Exception;
use LogicException;

class RegraException extends LogicException
{
    public function __construct(
        string $message,
        int $code = 0,
        Exception $previous = null,
        private readonly ?array $errors = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
