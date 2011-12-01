<?php

namespace ThinkBack\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use ThinkBack\MediaBundle\Entity\MediaTypes;
use ThinkBack\MediaBundle\Entity\MediaSelection;
use ThinkBack\MediaBundle\Entity\MediaSearch;
use ThinkBack\MediaBundle\Form\Type\MediaSelectionType;
use ThinkBack\MediaBundle\Form\Type\MediaSearchType;

class MediaController extends Controller
{
    private function getEntityManager(){
        return $this->getDoctrine()->getEntityManager();
    }
    
    
    /*
     * sets the decade and genre in a session that can be used
     * to set the values of the select options
     */
    private function setSessionData($data, $key){
        $session = $this->getRequest()->getSession();
        $session->set($key, $data);
        
    }
    
    private function getSessionData($key){
        $session = $this->getRequest()->getSession();
        
        if($session->has($key)){
            return $session->get($key);
        } else
            return null;
        
    }
    
    /*
     * the overall search functionality available on all pages
     */
    public function mediaSearchAction(Request $request = null){
        $key = 'mediaSearch';
        
        $em = $this->getEntityManager();
        $mediaSearch = new MediaSearch();
        $form = $this->createForm(new MediaSearchType(), $mediaSearch);
        
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            if($form->isValid()){
                $this->setSessionData($form->getData(), $key);
                //need to redirect to search results page showing the search title and listings
                return $this->redirect($this->generateUrl('mediaSearchResults', array(
                    'searchSlug'    => $mediaSearch->getSearchSlug(),
                    )));
            }
        }
        //just returns a partial segment of code to show the form for selecting media
        return $this->render('ThinkBackMediaBundle:Media:mediaSearchPartial.html.twig', array(
           'form' => $form->createView(), 
        ));
    }
    
    /*
     * called when a generic search is performed
     */
    public function mediaSearchResultsAction($searchSlug) {
        $data = $this->getSessionData('mediaSearch');
        return $this->render('ThinkBackMediaBundle:Media:mediaSearchResults.html.twig', array(
           'searchKeywords' => $data->getSearchKeywords(),
            
           //pass data to display
       ));
    }
    
    public function mediaSelectionAction(Request $request = null){
        $key = 'mediaSelection';
        $em = $this->getEntityManager();
        $mediaSelection = new MediaSelection();
        
        /*
         * if the data was posted before and is now saved in the session
         * retrieve it, merge it back into the entity manager (otherwise it 
         * throws the error 'entities must be managed' and use it to populate
         * the form, otherwise just use the empty media selection object
         */
        $sessionFormData = $this->getSessionData($key);
        if($sessionFormData != null){
            
            $decades = $sessionFormData->getDecades();
            $decades = $em->merge($decades);
            $mediaSelection->setDecades($decades);
            
            $genres = $sessionFormData->getGenres();
            $genres = $em->merge($genres);
            $mediaSelection->setGenres($genres);
        
        }

        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            if($form->isValid()){
                
                $this->setSessionData($form->getData(), $key);
                                
                return $this->redirect($this->generateUrl('mediaListings', array(
                    'decade'    => $mediaSelection->getDecades()->getSlug(),
                    'genre'     => $mediaSelection->getGenres()->getSlug(),
                    )));
                //'media'     => $mediaSelection->getMediaTypes()->getSlug(),
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
     * perform the search, then redirect to the listings action to show the results
     */
    public function mediaListingsAction($decade, $genre){
        
       return $this->render('ThinkBackMediaBundle:Media:mediaListings.html.twig', array(
           'decade' => $decade,
           'genre'  => $genre,
           //pass data to display
       ));
    }
    
    
    
    public function setSlugsAction($table){
        $em = $this->getEntityManager();
        switch($table){
            case "genre":
                $entities = $em->getRepository('ThinkBackMediaBundle:Genre')->findAll();
                foreach ($entities as $entity) {
                    $slug = strtolower($entity->getGenreName());
                    $slug = str_replace(',', '', $slug);
                    $slug = str_replace(' ', '-', $slug);
                    $entity->setSlug($slug);
                    $em->persist($entity);
                    $em->flush();
                }
                
                break;
            case "mediaType":
                $entities = $em->getRepository('ThinkBackMediaBundle:MediaType')->findAll();
                foreach ($entities as $entity) {
                    $slug = strtolower($entity->getMediaName());
                    $slug = str_replace(',', '', $slug);
                    $slug = str_replace(' ', '-', $slug);
                    $entity->setSlug($slug);
                    $em->persist($entity);
                    $em->flush();
                }
                break;
            case "decade":
                $entities = $em->getRepository('ThinkBackMediaBundle:Decade')->findAll();
                foreach ($entities as $entity) {
                    $slug = strtolower($entity->getDecadeName());
                    $slug = str_replace(',', '', $slug);
                    $slug = str_replace(' ', '-', $slug);
                    $entity->setSlug($slug);
                    $em->persist($entity);
                    $em->flush();
                }
                break;
        }
        
        return new Response('success');
        
    }
    
      static public function slugify($text)
      {
        // replace all non letters or digits by -
        $text = preg_replace('/\W+/', '-', $text);

        // trim and lowercase
        $text = strtolower(trim($text, '-'));

        return $text;
      }
    
}


?>