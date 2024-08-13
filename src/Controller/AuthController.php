<?php
// src/Controller/AuthController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Check if the user is already authenticated
        if ($this->getUser()) {
            return new JsonResponse(['message' => 'Already authenticated'], Response::HTTP_OK);
        }

        // If there's an error, return a JSON response with the error message
        if ($error) {
            return new JsonResponse([
                'message' => 'Authentication failed',
                'error' => $error->getMessageKey()
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Successful login logic here (e.g., returning a success message or user data)
        return new JsonResponse(['message' => 'Login successful'], Response::HTTP_OK);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // This method can be left blank - it will be intercepted by the logout key in your security configuration
        throw new \Exception('This method should not be reached.');
    }
}
