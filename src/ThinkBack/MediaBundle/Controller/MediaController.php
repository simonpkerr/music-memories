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
    
    
    /*
     * sets the media, decade and genre in a session that can be used
     * to set the values of the select options
     */
    private function setSessionData($data){
        $session = $this->getRequest()->getSession();
        /*$session->set('media', $mediaSelection->getMediaTypes()->getId());
        $session->set('decade', $mediaSelection->getDecades()->getId());
        $session->set('genre', $mediaSelection->getMediaTypes()->getId());
         * 
         */
        $session->set('mediaSelection', $data);
    }
    
    private function getSessionData(){
        $session = $this->getRequest()->getSession();
        
        if($session->has('mediaSelectionRequest')){
            /*return array(
                'media'     =>  $session->get('media', 0),
                'decade'    =>  $session->get('decade', 0),
                'genre'     =>  $session->get('genre', 0),
            );*/
            return array(
               'mediaSelection' => $session->get('mediaSelectionRequest'),
            );
        } else
            return null;
        
    }
    
    
    public function mediaSelectionAction(Request $request = null){
        $em = $this->getEntityManager();
        
        //get the various selection options
        $genres = $em->getRepository('ThinkBackMediaBundle:Genre')->getAllGenres();
                
        //add them to the mediaSelection object
        $mediaSelection = new MediaSelection();
        $mediaSelection->setGenres($genres);
        
        if($this->getSessionData()!=null){
            $data = $this->getSessionData();
            $mediaSelection->setDecades($data['decades']);
        }
        
        //$previousMediaSelections = $this->getSessionData();
        //if($previousMediaSelections != null)
        //    $form = $this->createForm(new MediaSelectionType(), $mediaSelection, $this->getSessionData());
        //else
            
        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        
        
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            if($form->isValid()){
                
                $data = $form->getData();
                $this->setSessionData($data);
                
                return $this->redirect($this->generateUrl('mediaSearchResults', array(
                    'decade'    => $mediaSelection->getDecades()->getDecadeName(),
                    'media'     => $mediaSelection->getMediaTypes()->getMediaName(),
                    'genre'     => $mediaSelection->getSelectedMediaGenres()->getGenreName(),
                    )));
                

            }
        }
        /*else {
            if($this->getSessionData() != null){
                $form->bind($this->getSessionData());
            }
        }*/
        
        //just returns a partial segment of code to show the form for selecting media
        return $this->render('ThinkBackMediaBundle:Media:mediaSelectionPartial.html.twig', array(
           'form' => $form->createView(), 
        ));
            
        
    }
    
    /*
     * perform the search, then redirect to the search action to show the results
     */
    public function mediaSearchResultsAction($decade, $media, $genre){
        
       return $this->render('ThinkBackMediaBundle:Media:mediaSearchResults.html.twig', array(
           'decade' => $decade,
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