<?php

namespace FinanceBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PagesController extends Controller
{
    /**
     * @Route("/") name="index"
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/shares") name="shares"
     * @Template()
     */
    public function sharesAction()
    {
        return [];
    }
}
