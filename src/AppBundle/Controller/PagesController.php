<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PagesController extends Controller
{
    /**
     * @Route("/") name="index"
     */
    public function indexAction()
    {
        return $this->render('views/pages/index.html.twig');
    }
}
