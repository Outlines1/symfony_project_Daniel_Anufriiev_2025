<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponseFormatter
{
    public function success(array $data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}
