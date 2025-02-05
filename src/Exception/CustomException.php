<?php
namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomException extends HttpException
{
    public function __construct(int $statusCode, string $message = 'Something went wrong', \Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $previous);
    }
}
