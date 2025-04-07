<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticationException extends BaseBusinessException
{
    protected $code = Response::HTTP_UNAUTHORIZED;

    public function __construct(
        string $message = 'Unauthenticated or invalid token.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        if ($code === 0)
            $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
