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

use ThinkBack\MediaBundle\MediaAPI;

class MediaController extends Controller
{
    //private $amazonapi;
    //private $youtubeapi;
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
     * if params exist, they should override the session values
     */
    private function getMediaSelection(array $params = null){
        $em = $this->getEntityManager();
        $mediaSelection = $this->getSessionData('mediaSelection');
        $mediaTypeSlug = null;
        $decadeSlug = null;
        $genreSlug = null;
        $keywords = null;
        $page = 1;
        
        if($params != null){
            $mediaTypeSlug = isset($params['media']) ? $params['media'] : MediaType::$default;
            $decadeSlug = isset($params['decade']) ? $params['decade'] : Decade::$default;
            $genreSlug = isset($params['genre']) ? $params['genre'] : Genre::$default;
            /*$mediaTypeSlug = isset($params['media']) ? $params['media'] : null;
            $decadeSlug = isset($params['decade']) ? $params['decade'] : null;
            $genreSlug = isset($params['genre']) ? $params['genre'] : null;*/
            $keywords = isset($params['keywords']) ? $params['keywords'] != '-' ? $params['keywords'] : null : null;
            $page = isset($params['page']) ? $params['page'] : 1;
        }
        
        if($mediaSelection == null)
            $mediaSelection = new MediaSelection();
        
                
        if($params != null){
            $mediaType = $em->getRepository('ThinkBackMediaBundle:MediaType')->getMediaTypeBySlug($mediaTypeSlug);
            if($mediaType == null)
                throw new NotFoundHttpException("There was a problem with that address");
           
            
            $mediaType = $this->getEntityManager()->merge($mediaType);
            $mediaSelection->setMediaTypes($mediaType);

            if($decadeSlug != Decade::$default){//if decade is not default decade
                $decade = $em->getRepository('ThinkBackMediaBundle:Decade')->getDecadeBySlug($decadeSlug);
                if($decade == null)
                    throw new NotFoundHttpException ("There was a problem with that address");
                
                $decade = $this->getEntityManager()->merge($decade);
                $mediaSelection->setDecades($decade);
            }else{
                //if the entity exists already, set to null so defaults to all-decades
                $mediaSelection->setDecades(null);
            }

            if($genreSlug != Genre::$default){
                try{
                    $genre = $em->getRepository('ThinkBackMediaBundle:Genre')->getGenreBySlugAndMedia($genreSlug, $mediaTypeSlug);
                }catch(\Exception $ex){
                    throw new NotFoundHttpException ("There was a problem with that address");
                }

                $genre = $this->getEntityManager()->merge($genre);
                $mediaSelection->setSelectedMediaGenres($genre);
            }else{
                $mediaSelection->setSelectedMediaGenres(null);
            }
        }

        if($keywords != null && $mediaSelection->getKeywords() == null){
            $mediaSelection->setKeywords($keywords);
        }

        if($mediaSelection->getPage() == null)
            $mediaSelection->setPage($page);
            
        $genres = $em->getRepository('ThinkBackMediaBundle:Genre')->getAllGenres();
        $mediaSelection->setGenres($genres);
            
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
                    'decade'    => $mediaSelection->getDecades() != null ? $mediaSelection->getDecades()->getSlug() : Decade::$default,
                    'media'     => $mediaSelection->getMediaTypes()->getSlug(),
                    'genre'     => $mediaSelection->getSelectedMediaGenres() != null ? $mediaSelection->getSelectedMediaGenres()->getSlug() : Genre::$default,
                    'keywords'  => $mediaSelection->getKeywords() != null ? $mediaSelection->getKeywords() : '-',
                    'page'      => $mediaSelection->getPage() != null ? $mediaSelection->getPage() : null,
            );
        }else {
            $params = array(
                'decade'    => Decade::$default,
                'media'     => MediaType::$default,
                'genre'     => Genre::$default,
                'page'      => 1,
            );
        }
        
        //array_filter removes elements from an array based on the function defined as the second argument
        $params = array_filter($params, array($this,'is_NotNull')); 
        return $params;
        
    }
    
    /*
     * gets the route for searching by
     */
    private function getSearchRoute(){
        
        $returnRoute = $this->generateUrl('search', array_filter(
                $this->getMediaSelectionParams(), array($this, "is_NotNull"))
        );
    }
    
    private function is_NotNull($v){
        return !is_null($v);
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
        
       $keywords = $keywords == '-' ? null : $keywords;
       $em = $this->getEntityManager();
        
       $pagerCount = 5;
       $pagerParams = array(
           'pagerCount' => $pagerCount,
       );
       
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
            $browseNodeArray = array(); 
            
            if($decade != Decade::$default){
                array_push($browseNodeArray, $em->getRepository('ThinkBackMediaBundle:Decade')->getDecadeBySlug($decade)->getAmazonBrowseNodeId());
            }
           
            if($genre != Genre::$default){
                $selectedGenre = $em->getRepository('ThinkBackMediaBundle:Genre')->getGenreBySlugAndMedia($genre,$media);
                $browseNodeArray = array_merge($browseNodeArray, array(
                    $selectedGenre->getAmazonBrowseNodeId(),
                    $selectedGenre->getMediaType()->getAmazonBrowseNodeId()));
            }else{
                array_push($browseNodeArray, $em->getRepository('ThinkBackMediaBundle:MediaType')->getMediaTypeBySlug($media)->getAmazonBrowseNodeId());
            }
            
            $canonicalBrowseNodes = implode(',', $browseNodeArray);
            
            $params = array_filter(array(
               'Keywords'       =>      $keywords,
               'BrowseNode'     =>      $canonicalBrowseNodes,
               'SearchIndex'    =>      'Video',
               'ItemPage'       =>      $page,
               'Sort'           =>      'salesrank',
            ), array($this, "is_NotNull"));
            
            //$this->amazonapi = $this->get('think_back_media.amazonapi');
            $this->mediaapi = $this->get('think_back_media.mediaapi');
            $this->mediaapi->setAPIStrategy('amazonapi');
            try{
                $response = $this->mediaapi->getRequest($params);
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
       
       $responseParams = array_filter(array(
           'decade'         => $decade,
           'genre'          => $genre,
           'media'          => $media,
           'keywords'       => $keywords,
           'pagerParams'    => $pagerParams,
       ), array($this, "is_NotNull"));
       
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
               'Operation'          =>      'ItemLookup',
               'ItemId'             =>      $id,
               'ResponseGroup'      =>      'Images,ItemAttributes,SalesRank,Request,Similarities',
                   //,RelatedItems',
               //'RelationshipType'   =>      'Season',  
            );
            //$this->amazonapi = $this->get('think_back_media.amazonapi');
            $this->mediaapi = $this->get('think_back_media.mediaapi');
            $this->mediaapi->setAPIStrategy('amazonapi');
            
            try{
                $response = $this->mediaapi->getRequest($params);
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
        //$this->youtubeapi = $this->get('think_back_media.youtubeapi');
        $this->mediaapi = $this->get('think_back_media.mediaapi');
        $this->mediaapi->setAPIStrategy('youtubeapi');
        $ytparams = array(
            'keywords'  =>  urldecode($title),
            'decade'    =>  $decade,
            'media'     =>  $media,
            'genre'     =>  $genre,
        );
        try{
            $ytResponse = $this->mediaapi->getRequest($ytparams);
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