<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class CannotDispenseAmountException extends BaseBusinessException
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct(
        string $message = 'The requested amount cannot be dispensed with available denominations or violates withdrawal rules.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        if ($code === 0)
            $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
