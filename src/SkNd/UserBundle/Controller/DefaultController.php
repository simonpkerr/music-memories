<?php
/*
 * Original code Copyright (c) 2013 Simon Kerr
 * DefaultController for linking to terms and conditions and other general things related to users
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function termsAndConditionsAction(){
        return $this->render('SkNdUserBundle:Registration:terms.html.twig');
    }

}


?>