<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class OperationFailedException extends BaseBusinessException
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function __construct(
        string $message = 'The requested operation could not be completed.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        if ($code === 0)
            $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
