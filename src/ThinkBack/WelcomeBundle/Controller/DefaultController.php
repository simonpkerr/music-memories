<?php

namespace ThinkBack\WelcomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ThinkBackWelcomeBundle:Default:index.html.twig');
    }
    
    public function errorAction(){
        return $this->render('ThinkBackWelcomeBundle:Default:error.html.twig');
    }

}


?>