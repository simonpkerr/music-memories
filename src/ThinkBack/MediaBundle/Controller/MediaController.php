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
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use ThinkBack\MediaBundle\Entity\MediaSelection;
use ThinkBack\MediaBundle\Entity\MediaSearch;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\Genre;
use ThinkBack\MediaBundle\Entity\MediaType;
use ThinkBack\MediaBundle\Form\Type\MediaSelectionType;
use ThinkBack\MediaBundle\Form\Type\MediaSearchType;
use ThinkBack\MediaBundle\MediaAPI\Utilities;

use ThinkBack\MediaBundle\MediaAPI;

class MediaController extends Controller
{
    //handles all calls to the various APIs
    private $mediaapi;
        
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
     * check session, return mediaSelection if not null
     * else create new media selection and return
     * $params - contains optional media, decade, genre, keywords, page
     * if params exist, they should override the session values if different
     */
    private function getMediaSelection(array $params = null){
        $em = $this->getEntityManager();
        $mediaTypeSlug = null;
        $decadeSlug = null;
        $genreSlug = null;
        $keywords = null;
        $page = 1;
        //try getting the media selection from the session
        $mediaSelection = $this->getSessionData('mediaSelection');        
        
        if($params != null){
            $mediaTypeSlug = isset($params['media']) ? $params['media'] : MediaType::$default;
            $decadeSlug = isset($params['decade']) ? $params['decade'] : Decade::$default;
            $genreSlug = isset($params['genre']) ? $params['genre'] : Genre::$default;
            $keywords = isset($params['keywords']) ? $params['keywords'] != '-' ? $params['keywords'] : null : null;
            $page = isset($params['page']) ? $params['page'] : $page;
        }
        
        if($mediaSelection == null)
            $mediaSelection = new MediaSelection();
        
        if($params != null){
            //only update the media type if its different
            if($mediaSelection->getMediaTypes() == null || $mediaTypeSlug != $mediaSelection->getMediaTypes()->getSlug())
                $mediaType = $em->getRepository('ThinkBackMediaBundle:MediaType')->getMediaTypeBySlug($mediaTypeSlug);
            else
                $mediaType = $mediaSelection->getMediaTypes();
            
            
            if($mediaType == null)
                throw new NotFoundHttpException("There was a problem with that address");
            
            $mediaType = $this->getEntityManager()->merge($mediaType);
            $mediaSelection->setMediaTypes($mediaType);
            
            
            //if decade is not default decade and is not the same as existing decade
            if($decadeSlug != Decade::$default){
                if($mediaSelection->getDecades() == null || $decadeSlug != $mediaSelection->getDecades()->getSlug())
                    $decade = $em->getRepository('ThinkBackMediaBundle:Decade')->getDecadeBySlug($decadeSlug);
                else
                    $decade = $mediaSelection->getDecades();
                
                if($decade == null)
                    throw new NotFoundHttpException ("There was a problem with that address");

                $decade = $this->getEntityManager()->merge($decade);
                $mediaSelection->setDecades($decade);
                //}
            }else{
                //if the entity exists already, set to null so defaults to all-decades
                $mediaSelection->setDecades(null);
            }

            if($genreSlug != Genre::$default){
                //if the genre is different or the media type is different, reset the genre
                if($mediaSelection->getSelectedMediaGenres() == null || $genreSlug != $mediaSelection->getSelectedMediaGenres()->getSlug() || $mediaTypeSlug != $mediaSelection->getMediaTypes()->getSlug())
                    try{
                        $genre = $em->getRepository('ThinkBackMediaBundle:Genre')->getGenreBySlugAndMedia($genreSlug, $mediaTypeSlug);
                    }catch(\Exception $ex){
                        throw new NotFoundHttpException ("There was a problem with that address");
                    }
                else 
                    $genre = $mediaSelection->getSelectedMediaGenres();

                $genre = $this->getEntityManager()->merge($genre);
                $mediaSelection->setSelectedMediaGenres($genre);
                //}
            }else{
                $mediaSelection->setSelectedMediaGenres(null);
            }
        }

        //if($keywords != null && $mediaSelection->getKeywords() == null){
        if($keywords != $mediaSelection->getKeywords()){
            $mediaSelection->setKeywords($keywords);
        }
        
        /*
         * if navigating from a listings page to a details page, the return route needs calculating
         * from a details page, the page number is not passed
         */
        
        //$mediaSelection->getPage() != null && 
        if(isset($params['page']))
            $mediaSelection->setPage($page);
            
        if($mediaSelection->getGenres() == null){
            $genres = $em->getRepository('ThinkBackMediaBundle:Genre')->getAllGenres();
            $mediaSelection->setGenres($genres);
        }
            
        //save the data so this process is only done once
        $this->setSessionData($mediaSelection, 'mediaSelection');
        
        return $mediaSelection;
    }
    
    
    /*
     * if no media, decade or genre is selected, defaults should be returned
     * so that the media selection form can be correctly set.
     */
    private function getMediaSelectionParams(){
        $mediaSelection = $this->getSessionData('mediaSelection');
        $params = array();
        if($mediaSelection != null){
            $params = array(
                    'media'     => $mediaSelection->getMediaTypes()->getSlug(),    
                    'decade'    => $mediaSelection->getDecades() != null ? $mediaSelection->getDecades()->getSlug() : Decade::$default,
                    'genre'     => $mediaSelection->getSelectedMediaGenres() != null ? $mediaSelection->getSelectedMediaGenres()->getSlug() : Genre::$default,
                    'keywords'  => $mediaSelection->getKeywords() != null ? $mediaSelection->getKeywords() : '-',
                    'page'      => $mediaSelection->getPage() != null ? $mediaSelection->getPage() : 1,
            );
        }else {
            $params = array(
                'media'     => MediaType::$default,
                'decade'    => Decade::$default,
                'genre'     => Genre::$default,
                'page'      => 1,
                'keywords'  => '-',
            );
        }
        
        //array_filter removes elements from an array based on the function defined as the second argument
        $params = $this->removeNullEntries($params);
        return $params;
        
    }
    
    private function removeNullEntries($params){
        return Utilities::removeNullEntries($params);
    }
    
    /*
     * gets the route for searching by
     */
    private function getSearchRoute(){
        
        $returnRoute = $this->generateUrl('search', $this->removeNullEntries($this->getMediaSelectionParams()));
        return $returnRoute;
    }
    
    
    
    /*
     * set the media to be searched on
     */
//    public function setMediaAction($mediaType){
//        if($mediaType == 'film' || $mediaType == 'tv'){
//            $this->setSessionData($mediaType, 'mediaType');
//        }
//        
//        //return to index whatever happens
//        return $this->redirect($this->generateUrl('index')); 
//    }
//    

    
    
    public function mediaSelectionAction(Request $request = null){
        $key = 'mediaSelection';
        $em = $this->getEntityManager();
 
        /*
         * if the data was posted before and is now saved in the session
         * retrieve it, merge it back into the entity manager (otherwise it 
         * throws the error 'entities must be managed' and use it to populate
         * the form, otherwise just use the empty media selection object
         */
        $mediaSelection = new MediaSelection();
        $sessionFormData = $this->getSessionData($key);
        if($sessionFormData != null){
            
            $mediaTypes = $sessionFormData->getMediaTypes();
            $mediaTypes = $this->getEntityManager()->merge($mediaTypes);
            $mediaSelection->setMediaTypes($mediaTypes);

            if($sessionFormData->getDecades() != null){
                $decades = $sessionFormData->getDecades();
                $decades = $em->merge($decades);
                $mediaSelection->setDecades($decades);
            }
            
            if($sessionFormData->getSelectedMediaGenres() != null){
                $selectedMediaGenres = $sessionFormData->getSelectedMediaGenres();
                $selectedMediaGenres = $this->getEntityManager()->merge($selectedMediaGenres);
                $mediaSelection->setSelectedMediaGenres($selectedMediaGenres);
            }
            
            if($sessionFormData->getKeywords() != null)
                $mediaSelection->setKeywords($sessionFormData->getKeywords());
        
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
                return $this->redirect($this->generateUrl('search', $this->getMediaSelectionParams()));
            }else{
                /*return $this->render('ThinkBackMediaBundle:Default:error.html.twig', array(
                    'form' => $form->createView(),
                ));*/
                return $this->redirect($this->generateUrl('error'));
            }
        }
       
        
        //just returns a partial segment of code to show the form for selecting media
        return $this->render('ThinkBackMediaBundle:Media:mediaSelectionPartial.html.twig', array(
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
       $mediaSelection = $this->getMediaSelection(array(
            'media'     => $media,
            'decade'    => $decade,
            'genre'     => $genre,
            'keywords'  => $keywords,
            'page'      => $page,
        ));
       
       $em = $this->getEntityManager();
        
       $pagerCount = 5;
       $pagerParams = array(
           'pagerCount' => $pagerCount,
       );
       
       if($media == "music"){
            //the correct parameters for a given API are retrieved through the mediaapi class
            /*$params = array(
                $em->getRepository('ThinkBackMediaBundle:Decade')->getDecadeBySlug($decade)->getSevenDigitalTag(),
                $genre != 'all' ? $em->getRepository('ThinkBackMediaBundle:Genre')->getGenreBySlug($genre)->getSevenDigitalTag(): '',
            );*/

            //todo
            $this->mediaapi = $this->get('think_back_media.mediaapi');
            $this->mediaapi->setAPIStrategy('sevendigitalapi');
            try{
                //$response = $sevenDigitalAPI->getRequest($params);
                $response = $this->mediaapi->getListings($mediaSelection);
            }catch(Exception $ex){
                $exception = $ex;
            }
       }else{
            $this->mediaapi = $this->get('think_back_media.mediaapi');
            $this->mediaapi->setAPIStrategy('amazonapi');
            try{
                $response = $this->mediaapi->getListings($mediaSelection);
                $pagerParams['pagerUpperBound'] = $response->Items->TotalPages > 10 ? 10 : $response->Items->TotalPages;
                $pagerParams['pagerLowerBound'] = 1;
                $pagerParams['totalPages'] = $pagerParams['pagerUpperBound'];
                $pagerParams['pagerRouteParams'] = $this->getMediaSelectionParams();
                //$pagerParams = array_merge($pagerParams, $this->calculatePagingBounds($pagerCount, $page));
            }catch(\RunTimeException $re){
                $exception = $re->getMessage();
            }catch(\LengthException $le){
                $exception = $le->getMessage();
            }
       }
       
       //if the page was set, set the page on the mediaSelection object
       $mediaSelection = $this->getSessionData('mediaSelection');
       $mediaSelection->setPage($page);
       $this->setSessionData($mediaSelection, 'mediaSelection');
       
       $responseParams = $this->removeNullEntries(array(
           'decade'         => $decade,
           'genre'          => $genre,
           'media'          => $media,
           'keywords'       => $keywords != '-' ? $keywords : null,
           'pagerParams'    => $pagerParams,
       ));
       
       if(!isset($exception))
           $responseParams['mainResponse'] = $response;
       else
           $responseParams['exception'] = $exception;
            
       return $this->render('ThinkBackMediaBundle:Media:searchResults.html.twig', $responseParams);
    }
     
    
    public function mediaDetailsAction($media, $decade, $genre, $id){
        /*
         * set the mediaSelection object if it doesn't exist - user may have gone straight to the page
         * without going through the selection process
         */
        $mediaSelection = $this->getMediaSelection(array(
            'media'     => $media,
            'decade'    => $decade,
            'genre'     => $genre,
        ));
                       
        //look up product
        if($media != 'music'){
            $params = array(
               'ItemId' =>  $id,
            );
            $this->mediaapi = $this->get('think_back_media.mediaapi');
            $this->mediaapi->setAPIStrategy('amazonapi');
            
            try{
                $response = $this->mediaapi->getDetails($params, $this->getMediaSelectionParams());
            }catch(\RunTimeException $re){
                $exception = $re->getMessage();
            }catch(\LengthException $le){
                $exception = $le->getMessage();
            }
        }
             
        $responseParams = array(
            'returnRouteParams' => $this->getMediaSelectionParams(),
            'media'             => $media,
            'decade'            => $decade,
            'genre'             => $genre,
        );
        
        //set the amazon response to either data or exception
        if(!isset($exception)){
            $responseParams['mainResponse'] = $response;
            if($media != 'music')
                $responseParams['title'] = $response->Items->Item->ItemAttributes->Title;
        }
        else
            $responseParams['exception'] = $exception;
     
        return $this->render('ThinkBackMediaBundle:Media:mediaDetails.html.twig', $responseParams);
        
    }
          
    public function youTubeRequestAction($title, $media, $decade, $genre){
        //look up YouTube
        $responseParams = array();
        
        //get the youtube service
        $this->mediaapi = $this->get('think_back_media.mediaapi');
        $this->mediaapi->setAPIStrategy('youtubeapi');
        $mediaSelection = $this->getMediaSelection(array(
            'media'     => $media,
            'decade'    => $decade,
            'genre'     => $genre,
        ));
        $mediaSelection->setKeywords(urldecode($title));
        
        try{
            $ytResponse = $this->mediaapi->getListings($mediaSelection);
        }catch(\RuntimeException $re){
            $ytException = $re->getMessage();
        }catch(\LengthException $le){
            $ytException = $le->getMessage();
        }
        //set the youtube response to either data or exception
        if(!isset($ytException))
            $responseParams['youTubeResponse'] = $ytResponse;
        else
            $responseParams['youTubeException'] = $ytException;
        
        return $this->render('ThinkBackMediaBundle:Media:youTubePartial.html.twig', $responseParams);        
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