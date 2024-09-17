<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    private JWTTokenManagerInterface $jwtManager;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher; // Make sure this is initialized
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['mdp'] ?? '';

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user && $this->passwordHasher->isPasswordValid($user, $password)) {
            $token = $this->jwtManager->create($user);
            return new JsonResponse([
                'message' => 'Login successful',
                'token' => $token
            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'message' => 'Authentication failed'
        ], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \Exception('This method should not be reached.');
    }
}
