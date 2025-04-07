<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InsufficientFundsException extends BaseBusinessException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function __construct(
        string $message = 'Insufficient funds available in the account.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        if ($code === 0)
            $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
