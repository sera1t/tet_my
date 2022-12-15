<?php

namespace App\Controller;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token\Builder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        $username = $this->getUser()->getUsername();

        $token = (new Builder())
        ->withClaim('mercure',  ['subscribe' => [sprintf('/%s', $username)]])
            ->getToken(
                new Sha256(),
                new Key($this->getParameter('mercury_secret_key'))
            )
        ;

        $response = $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);

        $response->headers->setCookie(
            new Cookie(
                'mercureAutorization',
                $token,
                (new \DateTime())
                ->add(new \DateInterval('PT2H')),
                '/.well-known/mercure',
                null,
                false,
                true,
                false,
                'strict'
            )
        );

        return $response;
    }
}
