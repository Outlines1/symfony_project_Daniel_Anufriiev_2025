<?php
namespace App\Controller;

use App\Exception\CustomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ApiTestController extends AbstractController
{
    #[Route('/api/test-error', name: 'api_test_error')]
    #[IsGranted('ROLE_ADMIN')]
    public function testError(): JsonResponse
    {
        throw new CustomException(400, 'This is a custom error message!');
    }
}
