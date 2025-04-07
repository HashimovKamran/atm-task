<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenAccessException extends BaseBusinessException
{
    protected $code = Response::HTTP_FORBIDDEN;

    public function __construct(
        string $message = 'Forbidden. You do not have permission to access this resource.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        if ($code === 0)
            $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
