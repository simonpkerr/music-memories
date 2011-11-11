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
    
    private function getMediaSelectForm(){
        $em = $this->getEntityManager();
        
        //get the various selection options
        $genres = $em->getRepository('ThinkBackMediaBundle:Genre')->getAllGenres();
        
        //add them to the mediaSelection object
        $mediaSelection = new MediaSelection();
        $mediaSelection->setGenres($genres);
                
        //create a form using the mediaSelectionType class and the data
        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        return $form;
    }
    
    public function mediaSelectionAction(){
                
        $form = $this->getMediaSelectForm();
        
        //just returns a partial segment of code to show the form for selecting media
        return $this->render('ThinkBackMediaBundle:Media:mediaSelectionPartial.html.twig', array(
           'form' => $form->createView(), 
        ));
            
        
    }
    
    /*
     * perform the search, then redirect to the search action to show the results
     */
    public function mediaSearchResultsAction(Request $request){
        $form = $this->getMediaSelectForm();
        
        //if the form was submitted
        $form->bindRequest($request);
        if($form->isValid()){
            
            $mediaSelection = $form->getData();
            //do the look up 
            return $this->render('ThinkBackMediaBundle:Media:mediaSearchResults.html.twig', array(
                'decade' => $mediaSelection->getDecades()->getDecadeName(),
            ));

        }
        
        return $this->render('ThinkBackMediaBundle:Media:mediaSelectionPartial.html.twig', array(
           'form' => $form->createView(), 
        ));
            
        
        
        /*return $this->render('ThinkBackMediaBundle:Media:mediaSearchResults.html.twig', array(
            'decade'    => $mediaSelection->getDecades()->getDecadeName(),
            'media'     => $mediaSelection->getMediaTypes()->getMediaName(),
            'genre'     => $mediaSelection->getSelectedMediaGenres(),
            //also pass data to be displayed
        ));*/
        //return new Response('decade =' . $mediaSelection->getDecades()->getDecadeName());
    }
    
}


?>