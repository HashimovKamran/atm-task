<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\OperationFailedException;
use App\Services\TransactionService\TransactionServiceInterface;

class TransactionController extends Controller
{
    public function __construct(protected TransactionServiceInterface $transactionService) {}

    public function destroy(int $id): Response
    {
        $deleted = $this->transactionService->delete($id);
        if (!$deleted)
            throw new OperationFailedException('Failed to delete the transaction.');
        return response()->noContent();
    }
}
