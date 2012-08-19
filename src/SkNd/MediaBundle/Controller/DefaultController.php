<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * DefaultController controls simple aspects of showing the index action and showing the error action.
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        //$mediaType = $this->getRequest()->getSession()->get('mediaType') != null ? $this->getRequest()->getSession()->get('mediaType') : 'film';
        return $this->render('SkNdMediaBundle:Default:index.html.twig');
    }
    
    public function errorAction(){
        return $this->render('SkNdMediaBundle:Default:error.html.twig');
    }

}


?>