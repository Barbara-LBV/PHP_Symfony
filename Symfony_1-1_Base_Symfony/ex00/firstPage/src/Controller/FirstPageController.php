<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FirstPageController extends AbstractController
{
    #[Route('/e00/firstPage', name: 'firstpage')]
    public function show(): Response
    {
        $render = $this->render('firstpage.html.twig');
        return $render;
    }
    
}
?>