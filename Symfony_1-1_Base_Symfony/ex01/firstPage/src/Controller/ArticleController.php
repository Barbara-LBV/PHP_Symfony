<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/e01/', name: 'home')]
    public function showHome(): Response
    {
        $render = $this->render('home.html.twig');
        return $render;
    }

    #[Route('/e01/deltoro', name: 'deltoro')]
    public function showDelToro(): Response
    {
        $render = $this->render('articleDelToro.html.twig');
        return $render;
    }

    #[Route('/e01/miziaki', name: 'miziaki')]
    public function showMiziaki(): Response
    {
        $render = $this->render('articleMiazaki.html.twig');
        return $render;
    }

    #[Route('/e01/jackson', name: 'jackson')]
    public function showJackson(): Response
    {
        $render = $this->render('articleJackson.html.twig');
        return $render;
    }
    // Wrong url
//    #[Route('/e01/wrongUrl', name: 'wrongurl')]
//    public function showWrongurl(): Response
//    {
//        $render = $this->render('home.html.twig');
//        return $render;
//    }

}
?>
