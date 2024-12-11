<?php

namespace Anil\FastApiCrud\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiCrudException extends Exception
{
    /**
     * Render the exception.
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $this->getMessage(),
                'file' => $this->getFile(),
                'line' => $this->getLine(),

            ],
        ], $this->getCode());
    }
}
