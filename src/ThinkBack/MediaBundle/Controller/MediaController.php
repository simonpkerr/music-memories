<?php

namespace ThinkBack\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use ThinkBack\MediaBundle\Entity\MediaTypes;
use ThinkBack\MediaBundle\Entity\MediaSelection;
use ThinkBack\MediaBundle\Form\Type\MediaSelectionType;

class MediaController extends Controller
{
    private function getEntityManager(){
        return $this->getDoctrine()->getEntityManager();
    }
    
    public function mediaSelectionAction(Request $request = null){
        $em = $this->getEntityManager();
        
        //get the various selection options
        $genres = $em->getRepository('ThinkBackMediaBundle:Genre')->getAllGenres();
        
        //add them to the mediaSelection object
        $mediaSelection = new MediaSelection();
        $mediaSelection->setGenres($genres);
                
        //create a form using the mediaSelectionType class and the data
        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        
        //if the form was submitted
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            if($form->isValid()){
                //pass to search action that then redirects
                return $this->forward('ThinkBackMediaBundle:Media:mediaSearch', array(
                   'mediaSelection' => $mediaSelection, 
                ));
                
            }
            
        }else{
        
            return $this->render('ThinkBackMediaBundle:Media:mediaSelectionPartial.html.twig', array(
               'form' => $form->createView(), 
            ));
            
        }
    }
    
    /*
     * perform the search, then redirect to the search action to show the results
     */
    public function mediaSearchAction($mediaSelection){
        /*return $this->redirect($this->generateUrl('mediaSearch', array(
                    'decade'    => $mediaSelection->getDecades()->getDecadeName(),
                    'media'     => $mediaSelection->getMediaTypes()->getMediaName(),
                    'genre'     => $mediaSelection->getSelectedMediaGenres(),
                    ))
                );*/
        return new Response('decade =' . $mediaSelection->getDecades()->getDecadeName());
    }
    
}


?>