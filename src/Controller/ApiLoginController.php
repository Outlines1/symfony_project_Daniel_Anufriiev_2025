<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user, TokenStorageInterface $tokenStorage): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Missing credentials or user not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }


        $secretKey = 'your-secret-key-here';


        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $user->getId(),
            'username' => $user->getUserIdentifier()
        ];


        try {

            $jwt = JWT::encode($payload, $secretKey, 'HS256');
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error generating the token',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $jwt,
        ]);
    }
}
