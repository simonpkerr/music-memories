<?php

namespace ThinkBack\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ThinkBackMediaBundle:Default:index.html.twig');
    }
    
    public function errorAction(){
        return $this->render('ThinkBackMediaBundle:Default:error.html.twig');
    }

}


?>