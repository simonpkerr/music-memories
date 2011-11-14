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
        $session->set('mediaSelection', $data);
        
    }
    
    private function getSessionData(){
        $session = $this->getRequest()->getSession();
        
        if($session->has('mediaSelection')){
            return $session->get('mediaSelection');
        } else
            return null;
        
    }
    
    public function mediaSelectionAction(Request $request = null){
        $em = $this->getEntityManager();
        $mediaSelection = new MediaSelection();
        
        /*
         * if the data was posted before and is now saved in the session
         * retrieve it, merge it back into the entity manager (otherwise it 
         * throws the error 'entities must be managed' and use it to populate
         * the form, otherwise just use the empty media selection object
         */
        $sessionFormData = $this->getSessionData();
        if($sessionFormData != null){
            $mediaTypes = $sessionFormData->getMediaTypes();
            $mediaTypes = $this->getEntityManager()->merge($mediaTypes);
            $mediaSelection->setMediaTypes($mediaTypes);
            
            $decades = $sessionFormData->getDecades();
            $decades = $this->getEntityManager()->merge($decades);
            $mediaSelection->setDecades($decades);
            
            $selectedMediaGenres = $sessionFormData->getSelectedMediaGenres();
            $selectedMediaGenres = $this->getEntityManager()->merge($selectedMediaGenres);
            $mediaSelection->setSelectedMediaGenres($selectedMediaGenres);
            
            $mediaSelection->setGenres($sessionFormData->getGenres());
        }else{
            $genres = $em->getRepository('ThinkBackMediaBundle:Genre')->getAllGenres();
            $mediaSelection->setGenres($genres);
        }
        
        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            if($form->isValid()){
                
                $this->setSessionData($form->getData());
                                
                return $this->redirect($this->generateUrl('mediaSearchResults', array(
                    'decade'    => $mediaSelection->getDecades()->getDecadeName(),
                    'media'     => $mediaSelection->getMediaTypes()->getMediaName(),
                    'genre'     => $mediaSelection->getSelectedMediaGenres()->getGenreName(),
                    )));
            }else{
                return $this->render('ThinkBackMediaBundle:Default:error.html.twig', array(
                    'form' => $form->createView(),
                ));
            }
        }
       
        
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