<?php

namespace ThinkBack\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use ThinkBack\MediaBundle\Entity\MediaTypes;

class MediaController extends Controller
{
    private function getEntityManager(){
        return $this->getDoctrine()->getEntityManager();
    }
    
    public function loadMediaSelectionArgumentsAction()
    {
        $em = $this->getEntityManager();
        
        $mediaTypes = $em->getRepository('ThinkBackMediaBundle:MediaType')->getMediaTypes();
        $decades = $em->getRepository('ThinkBackMediaBundle:Decade')->getDecades();
        if($mediaTypes != null){
            return $this->render('ThinkBackMediaBundle:Media:mediaSelectionPartial.html.twig', array('mediaTypes' => $mediaTypes, 'decades' => $decades));
        }
        else {
            return $this->forward('ThinkBackMediaBundle:Default:error');
        }
    }
    
}


?>