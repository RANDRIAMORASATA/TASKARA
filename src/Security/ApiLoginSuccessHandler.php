<?php
// src/Security/ApiLoginSuccessHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
// Remove the unused import statement
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ApiLoginSuccessHandler implements \Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface
{
    public function onAuthenticationSuccess(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token): \Symfony\Component\HttpFoundation\JsonResponse
    {
        // Return a success response
        return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'success', 'message' => 'Login successfullllll'], \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
