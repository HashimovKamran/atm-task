<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

abstract class BaseBusinessException extends Exception
{
    public function render(): JsonResponse
    {
        Log::error(
            'Business exception occurred: ' . $this->getMessage(),
            [
                'code' => $this->getCode(),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTraceAsString(),
            ]
        );

        return response()->json([
            'error' => $this->getMessage(),
        ], $this->getCode());
    }
}
