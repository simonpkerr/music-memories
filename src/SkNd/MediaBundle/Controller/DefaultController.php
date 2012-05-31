<?php

namespace SkNd\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $mediaType = $this->getRequest()->getSession()->get('mediaType') != null ? $this->getRequest()->getSession()->get('mediaType') : 'film';
        return $this->render('SkNdMediaBundle:Default:index.html.twig',
                array(
                    'mediaType' => $mediaType,
                ));
    }
    
    public function errorAction(){
        return $this->render('SkNdMediaBundle:Default:error.html.twig');
    }

}


?>