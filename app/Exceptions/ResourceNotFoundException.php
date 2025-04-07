<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ResourceNotFoundException extends BaseBusinessException
{
    protected $code = Response::HTTP_NOT_FOUND;

    public function __construct(
        string $message = 'The requested resource was not found.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        if ($code === 0)
            $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
