<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class WithdrawalLimitException extends BaseBusinessException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function __construct(
        string $message = 'Withdrawal amount exceeds the allowed limit.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        if ($code === 0)
            $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
