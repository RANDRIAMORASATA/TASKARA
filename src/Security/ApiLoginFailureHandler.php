<?php
// src/Security/ApiLoginFailureHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; // Assurez-vous d'importer la bonne classe Response

class ApiLoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(
            ['status' => 'error', 'message' => $exception->getMessageKey()],
            Response::HTTP_UNAUTHORIZED // Utilisation de la constante correcte
        );
    }
}
