<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class HomeController extends AbstractController
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    #[Route('/home', name: 'app_home')]
    public function index(): Response
    { 
       
        if( (is_null($this->getUser())) || ($this->getUser() == "")){
            return $this->redirectToRoute('app_login');
        }else{
             $token = $this->getToken($this->getUser());
            return $this->render('home/index.html.twig', ["token"=>$token]);
        }
    }
    

    public function getToken(UserInterface $user)
    {
        $token = $this->jwtManager->create($user);

        // Devuelve el token como respuesta
        return $token;
    }



}
