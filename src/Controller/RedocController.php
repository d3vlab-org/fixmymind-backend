<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedocController extends AbstractController
{
    #[Route('/redoc', name: 'app_redoc')]
    public function index(): Response
    {
        return $this->render('redoc.html.twig');
    }
}