<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaController controls all aspects of connecting to and displaying media
 * @author Simon Kerr
 * @version 1.0
 */

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

use ThinkBack\MediaBundle\Resources\MediaAPI;

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
     * set the media to be searched on
     */
    public function setMediaAction($mediaType){
        if($mediaType == 'film' || $mediaType == 'tv'){
            $this->setSessionData($mediaType, 'mediaType');
        }
        
        //return to index whatever happens
        return $this->redirect($this->generateUrl('index')); 
    }
    
    /*
     * the overall search functionality available on all pages
     */
    public function mediaSearchAction(Request $request = null){
        $key = 'mediaSearch';
        
       // $em = $this->getEntityManager();
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
            $mediaTypes = $sessionFormData->getMediaTypes();
            $mediaTypes = $this->getEntityManager()->merge($mediaTypes);
            $mediaSelection->setMediaTypes($mediaTypes);
            
            $decades = $sessionFormData->getDecades();
            $decades = $em->merge($decades);
            $mediaSelection->setDecades($decades);
            
            if($sessionFormData->getSelectedMediaGenres() != null){
                $selectedMediaGenres = $sessionFormData->getSelectedMediaGenres();
                $selectedMediaGenres = $this->getEntityManager()->merge($selectedMediaGenres);
                $mediaSelection->setSelectedMediaGenres($selectedMediaGenres);
            }
            
            $mediaSelection->setGenres($sessionFormData->getGenres());
        
        }else{
            $genres = $em->getRepository('ThinkBackMediaBundle:Genre')->getAllGenres();
            $mediaSelection->setGenres($genres);
        }

        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            if($form->isValid()){
                
                $this->setSessionData($form->getData(), $key);
                return $this->redirect($this->generateUrl('mediaListings', $this->getMediaSelection()));
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
    public function mediaListingsAction($decade, $media, $genre, $page = 1){
       $pagerCount = 5;
       $pagerParams = array(
           'pagerCount' => $pagerCount,
       );
       
       $em = $this->getEntityManager();
       $exception = null; 
       
       if($media == "music"){
            $params = array(
                $em->getRepository('ThinkBackMediaBundle:Decade')->getDecadeBySlug($decade)->getSevenDigitalTag(),
                $genre != 'all' ? $em->getRepository('ThinkBackMediaBundle:Genre')->getGenreBySlug($genre)->getSevenDigitalTag(): '',
            );

            $api = new MediaAPI\SevenDigitalAPI();
            try{
                $response = $sevenDigitalAPI->getRequest($params);
            }catch(Exception $ex){
                $exception = $ex;
            }
       }else{
            $browseNode = $em->getRepository('ThinkBackMediaBundle:Decade')->getDecadeBySlug($decade)->getAmazonBrowseNodeId();
            $selectedGenre = $em->getRepository('ThinkBackMediaBundle:Genre')->getGenreBySlugAndMedia($genre,$media);
            $browseNode .= $genre != 'all' ? ',' . $selectedGenre->getAmazonBrowseNodeId() : '';
            $browseNode .= ',' . $selectedGenre->getMediaType()->getAmazonBrowseNodeId();
            $params = array(
               'BrowseNode'     =>      $browseNode,
               'SearchIndex'    =>      'Video',
               'ItemPage'       =>      $page,
            );
            $api = new MediaAPI\AmazonAPI($this->container);
            try{
                $response = $api->getRequest($params);
                $pagerParams['pagerUpperBound'] = $response->Items->TotalPages > 10 ? 10 : $response->Items->TotalPages;
                $pagerParams['pagerLowerBound'] = 1;
                $pagerParams['totalPages'] = $pagerParams['pagerUpperBound'];
                //$pagerParams = array_merge($pagerParams, $this->calculatePagingBounds($pagerCount, $page));
            }catch(\RunTimeException $re){
                $exception = $re->getMessage();
            }catch(\LengthException $le){
                $exception = $le->getMessage();
            }
       }
       
       $this->setSessionData($page, 'currentPage');
            
       return $this->render('ThinkBackMediaBundle:Media:mediaListings.html.twig', array(
           'decade'     => $decade,
           'genre'      => $genre,
           'media'      => $media,
           'data'       => $exception == null ? $response : null,
           'exception'  => $exception,
           'pagerParams'=> $pagerParams,
       ));
    }
    
    private function calculatePagingBounds($pagerCount, $currentPage){
        $pagerUpperBound = $pagerCount * (floor($currentPage / $pagerCount)+1);
        $pagerLowerBound = $pagerUpperBound - ($pagerCount*2) <= 0 ? 1 : $pagerUpperBound - ($pagerCount*2);
        
        return array(
            'pagerUpperBound'   => $pagerUpperBound,
            'pagerLowerBound'   => $pagerLowerBound,
        );
  
    }
    
    public function mediaDetailsAction($id, $title, $media){
        $exception = null;

        //create the return route back to the correct page of listings
        $mediaSelection = $this->getSessionData('mediaSelection');
        $page = $this->getSessionData('currentPage');
        $returnRoute = $this->generateUrl('mediaListings', array_merge($this->getMediaSelection(), array('page' => $page)));
        
        //look up product
        if($media != 'music'){
            $params = array(
               'Operation'      =>      'ItemLookUp',
               'ItemId'         =>      $id,
               'SearchIndex'    =>      'Video',
               'ResponseGroup'  =>      'Images,ItemAttributes,SalesRank,Request,Similarities',
            );
            $api = new MediaAPI\AmazonAPI($this->container);
            try{
                $response = $api->getRequest($params);
            }catch(\RunTimeException $re){
                $exception = $re->getMessage();
            }catch(\LengthException $le){
                $exception = $le->getMessage();
            }
        }
        
        //look up YouTube
        
        
        //look up Flickr
        
        return $this->render('ThinkBackMediaBundle:Media:mediaDetails.html.twig', array(
            'data'           => $exception == null ? $response : null,
            'exception'      => $exception,
            'returnRoute'    => $returnRoute,
            'media'          => $media,
            'title'          => $title,
       ));
        
    }
    
    
    private function getMediaSelection(){
        $mediaSelection = $this->getSessionData('mediaSelection');
        return array(
                    'decade'    => $mediaSelection->getDecades()->getSlug(),
                    'media'     => $mediaSelection->getMediaTypes()->getSlug(),
                    'genre'     => $mediaSelection->getSelectedMediaGenres() != null ? $mediaSelection->getSelectedMediaGenres()->getSlug() : 'all',
                    );
    }
    
    /*public function setSlugsAction($table){
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
        
    }*/
    
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