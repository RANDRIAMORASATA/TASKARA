<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CorsListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        // Ajoutez vos règles CORS ici
        if ($request->getMethod() === 'OPTIONS') {
            // Permettre les requêtes pré-flight
            $event->getResponse()->headers->set('Access-Control-Allow-Origin', '*');
            $event->getResponse()->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $event->getResponse()->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $event->getResponse()->setStatusCode(204); // No Content
            return;
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        // Ajoutez vos en-têtes CORS ici
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
