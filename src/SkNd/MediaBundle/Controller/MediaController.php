<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaController controls all aspects of connecting to and displaying media
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaSearch;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\MediaType;
use SkNd\MediaBundle\Form\Type\MediaSelectionType;
use SkNd\MediaBundle\Form\Type\MediaSearchType;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\MediaAPI\MediaAPI;

class MediaController extends Controller
{
    //handles all calls to the various APIs
    private $mediaapi;
        
    private function getEntityManager(){
        return $this->getDoctrine()->getEntityManager();
    }
   
    /*
     * gets the route for searching by
     */
    private function getSearchRoute(){
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $returnRoute = $this->generateUrl('search', $this->mediaapi->getMediaSelectionParams());
        return $returnRoute;
    }
    
    public function mediaSelectionAction(Request $request = null){
        $session = $this->getRequest()->getSession();
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $em = $this->mediaapi->getEntityManager();
 
        /*
         * if the data was posted before and is now saved in the session
         * retrieve it, merge it back into the entity manager (otherwise it 
         * throws the error 'entities must be managed' and use it to populate
         * the form, otherwise just use the empty media selection object
         */
        $mediaSelection = new MediaSelection();
        $sessionFormData = $session->get('mediaSelection');
        if($sessionFormData != null){
            
            $mediaType = $sessionFormData->getMediaType();
            $mediaType = $em->merge($mediaType);
            $mediaSelection->setMediaType($mediaType);

            if($sessionFormData->getDecade() != null){
                $decade = $sessionFormData->getDecade();
                $decade = $em->merge($decade);
                $mediaSelection->setDecade($decade);
            }
            
            if($sessionFormData->getSelectedMediaGenre() != null){
                $selectedMediaGenre = $sessionFormData->getSelectedMediaGenre();
                $selectedMediaGenre = $em->merge($selectedMediaGenre);
                $mediaSelection->setSelectedMediaGenre($selectedMediaGenre);
            }
            
            if($sessionFormData->getKeywords() != null)
                $mediaSelection->setKeywords($sessionFormData->getKeywords());
        
            $mediaSelection->setGenres($sessionFormData->getGenres());
            
            
        }else{
            $genres = $em->getRepository('SkNdMediaBundle:Genre')->getAllGenres();
            $mediaSelection->setGenres($genres);
        }
        
        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            if($form->isValid()){
                
                $mediaSelection = $form->getData();
                $this->mediaapi->setMediaSelection($mediaSelection);
                
                return $this->redirect($this->generateUrl('search', $this->mediaapi->getMediaSelectionParams()));
            }else{
                return $this->redirect($this->generateUrl('error'));
            }
        }
        
        //just returns a partial segment of code to show the form for selecting media
        return $this->render('SkNdMediaBundle:Media:mediaSelectionPartial.html.twig', array(
           'form' => $form->createView(), 
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
    
    /*
     * perform the search, then redirect to the listings action to show the results
     */
    public function searchAction($media, $decade = "all-decades", $genre = "all-genres", $keywords = '-', $page = 1){
       $this->mediaapi = $this->get('sk_nd_media.mediaapi');
       $mediaSelection = $this->mediaapi->getMediaSelection(array(
           'api'       => 'amazonapi', 
           'media'     => $media,
           'decade'    => $decade,
           'genre'     => $genre,
           'keywords'  => $keywords,
           'page'      => $page,
           'computedKeywords'  => null,
        ));
       
       $pagerCount = 5;
       $pagerParams = array(
           'pagerCount' => $pagerCount,
       );
       
       $responseParams = Utilities::removeNullEntries(array(
           'decade'         => $decade,
           'genre'          => $genre,
           'media'          => $media,
           'keywords'       => $keywords != '-' ? $keywords : null,
           'api'            => 'amazonapi',
       ));
       
       //todo
       if($media == "music"){
            
            $this->mediaapi->setAPIStrategy('sevendigitalapi');
            try{
                $response = $this->mediaapi->getListings();
            }catch(Exception $ex){
                $exception = $ex;
            }
       }else{
            $this->mediaapi->setAPIStrategy('amazonapi');
            try{
                $listings = $this->mediaapi->getListings(MediaAPI::MEMORY_WALL_RECOMMENDATION);
                $response = $listings['response'];
                $pagerParams['pagerUpperBound'] = $response->TotalPages > 10 ? 10 : $response->TotalPages;
                $pagerParams['pagerLowerBound'] = 1;
                $pagerParams['totalPages'] = $pagerParams['pagerUpperBound'];
                $pagerParams['pagerRouteParams'] = $this->mediaapi->getMediaSelectionParams();
                $responseParams['pagerParams'] = $pagerParams;
                
                $responseParams = array_merge($responseParams, $listings);
                //$pagerParams = array_merge($pagerParams, $this->calculatePagingBounds($pagerCount, $page));
            }catch(\RunTimeException $re){
                $this->get('session')->setFlash('amazon-notice', 'media.amazon.runtime_exception');
            }catch(\LengthException $le){
                $this->get('session')->setFlash('amazon-notice', 'media.amazon.length_exception');
            }
       }
       
       return $this->render('SkNdMediaBundle:Media:searchResults.html.twig', $responseParams);
    }

    public function mediaDetailsAction($id, $media, $decade = 'all-decades', $genre = 'all-genres'){
        /*
         * set the mediaSelection object if it doesn't exist - user may have gone straight to the page
         * without going through the selection process
         */
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $mediaSelection = $this->mediaapi->getMediaSelection(array(
            'api'               => 'amazonapi',
            'media'             => $media,
            'decade'            => $decade,
            'genre'             => $genre,
            'computedKeywords'  => null,
        ));
        
        $details = null;
        $title = null;
        
        $referrer = $this->getRequest()->headers->get('referer');
        
        $responseParams = array(
            'media'             => $media,
            'decade'            => $decade,
            'genre'             => $genre,
            'api'               => 'amazonapi',
            'referrer'          => $referrer,
        );
        
        if($media != 'music'){
            $params = array(
               'ItemId' =>  $id,
            );
                       
            try{               
                $responseParams['mediaResource'] = $this->mediaapi->getDetails($params, MediaAPI::MEDIA_RESOURCE_RECOMMENDATION);
                                
            }catch(\RunTimeException $re){
                $this->get('session')->setFlash('amazon-notice', 'media.amazon.runtime_exception');
            }catch(\LengthException $le){
                $this->get('session')->setFlash('amazon-notice', 'media.amazon.length_exception');
            }
        }
       
        return $this->render('SkNdMediaBundle:Media:mediaDetails.html.twig', $responseParams);
        
    }
          
    public function youTubeRequestAction($title, $media, $decade, $genre){
        $responseParams = array();
        
        //get the youtube service
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $responseParams['api'] = $this->mediaapi->getCurrentAPI()->getName();
        $mediaSelection = $this->mediaapi->getMediaSelection(array(
            'api'               => 'youtubeapi',
            'media'             => $media,
            'decade'            => $decade,
            'genre'             => $genre,
            'computedKeywords'  => urldecode($title),
        ));
        
        $listings = null;
        try{
            $listings = $this->mediaapi->getListings();
            //merge the listings and responseParams and remove null entries
            $responseParams = Utilities::removeNullEntries(array_merge($responseParams, $listings));
        }catch(\RuntimeException $re){
            $this->get('session')->setFlash('yt-notice', 'media.youtube.runtime_exception');
        }catch(\LengthException $le){
            $this->get('session')->setFlash('yt-notice', 'media.youtube.length_exception');
        }

        return $this->render('SkNdMediaBundle:Media:youTubePartial.html.twig', $responseParams);        
    }
    
}


?>