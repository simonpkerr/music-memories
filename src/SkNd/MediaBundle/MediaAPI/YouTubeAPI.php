<?php
/*
 * Copyright (c) 2011 Simon Kerr
 * Connects to YouTube api to return results for all media
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
//require_once 'Zend/Loader.php';
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\MediaSelection;
use \SimpleXMLElement;

class YouTubeAPI implements IAPIStrategy {
    const FRIENDLY_NAME = 'YouTube';
    public $API_NAME = 'youtubeapi';
    
    protected $youTube;
    private $query;
    
    public function __construct($youtube_request_object = null){
        
        //get access to the youtube methods
        //\Zend_Loader::loadClass('Zend_Gdata_YouTube');
       
        $this->youTube = $youtube_request_object == null ? new \Zend_Gdata_YouTube() : $youtube_request_object;
        $this->youTube->setMajorProtocolVersion(2);
       
        $this->youTube = new \Zend_Gdata_YouTube();
        
        //$vf = $this->youTube->getVideoFeed();
        //$vf->getE
    }
    
    public function getName(){
        return $this->API_NAME;
    }
    
    public function setRequestObject($obj){
        $this->youTube = $obj;
    }
    
    /*
     * for youtube, details are retrieved on the client,
     * but still need to be stored to drive recommendations, timeline
     * and improve memory walls
     */
    public function getDetails(array $params){
        if(!isset($params['ItemId']))
            throw new \InvalidArgumentException('No id was passed to Youtube');
        
        $ve = $this->youTube->getVideoEntry($params['ItemId']);
        
        if($ve === false)
            throw new \RuntimeException("Could not connect to YouTube");
        
        if(count($ve) < 1){
            throw new \LengthException("No results were returned");
        }
        
        $response = $this->constructVideoEntry(new SimpleXMLElement('<entry></entry>'), $ve);
        return $response;
        
                
    }
    public function doBatchProcess(array $ids){
        //construct a batch feed 
        /*$feed = new \Zend_Gdata_YouTube_VideoFeed();
        $feed->registerNamespace('batch', "http://schemas.google.com/gdata/batch");
        
        foreach($ids as $id){
            $entry = new \Zend_Gdata_YouTube_VideoEntry();
            $entry->setId($id);
            $feed->addEntry($entry);
        }
        $response = $this->youTube->post($feed->__toString(), 'http://gdata.youtube.com/feeds/api/videos/batch');
        
        return $response;*/
        
        $feed = '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/"
xmlns:batch="http://schemas.google.com/gdata/batch" xmlns:yt="http://gdata.youtube.com/schemas/2007"><batch:operation type="query" />';
        //$b = $feed->addChild('batch:operation type="query"');
        
        $entries = array();
        foreach($ids as $id){
            array_push($entries, '<entry><id>http://gdata.youtube.com/feeds/api/videos/' . $id . '</id></entry>');
        }
        $feed = $feed . implode('', $entries) . '</feed>';
        try{
            $response = $this->youTube->post($feed, 'http://gdata.youtube.com/feeds/api/videos/batch');
        }catch(\Exception $ex){
            throw $ex;
        }
        
        if($response === false)
            throw new \RuntimeException('could not connect to YouTube');
        
        if($response->getStatus() != 200)
            throw new \RuntimeException('Problem loading results from YouTube');
        
        $response = $response->getBody();//gets the raw response
        $feed = new \Zend_Gdata_YouTube_VideoFeed();
        $feed->transferFromXML($response);
        /************* THIS NEEDS WORK TO TRANSFORM FOR PASSING BACK*/
        
        return $response;
        
    }
    
    public function getId(SimpleXMLElement $xmlData){}
    
    public function getImageUrlFromXML(SimpleXMLElement $xmlData) {
        try{
            return (string)$xmlData->thumbnail;
        } catch(\RuntimeException $re){
            return null;
        }
    }
    public function getItemTitleFromXML(SimpleXMLElement $xmlData){
        try{
            return (string)$xmlData->title;
        } catch(\RuntimeException $re){
            return null;
        }
    }
    
    public function getListings(MediaSelection $mediaSelection){
                
        //------to send a simple query to youtube       
        //$query->setVideoQuery($keywordQuery);
        
        //------to set up a category and keyword search
        
        /*$categories = '/Music|';
        if($params['media'] == 'film')
            $categories .= 'Film|Entertainment';
        else
            $categories .= '|Entertainment';
       */
        
        //$keywordQuery = str_replace(' ', '/', $keywordQuery) . $categories;
        //$query->setCategory($keywordQuery);
        
        //$query->setCategory("Film/Entertainment/" . $keywordQuery);
        /*$searchTermsArray =  $params['keywords']
        foreach ($searchTermsArray as $searchTerm) {
          $keywordQuery .= strtolower($searchTerm) . '/';
        }*/
        //$query->setCategory($keywordQuery);
        
        
        /*
         * what happens if the search still returns no videos. 
         * for example, the moomins 1970s tv returns no results 
         * but the moomins does.However, certain searches need more specific
         * keywords to specify the decade and media type
         */
        
        //$this->getVideoFeed($query->getQueryUrl(2));
                
        $videoFeed = $this->getVideoFeed($mediaSelection);

        if($videoFeed === false)
            throw new \RuntimeException("Could not connect to YouTube");
        
        //try a secondary search only if this is the first time only a few results were returned
        
        //the inclusion from MediaAPI of decade and genre in the youtube search string seems to be irrelevant in many cases
        //so this part has been removed
        /*if(count($videoFeed) < 3){
            //setting the secondarySearch param causes a different keyword search to be used
            $params['secondarySearch'] = true;
            $videoFeed = $this->getVideoFeed($params);
            if($videoFeed === false)
                throw new \RuntimeException("Could not connect to YouTube");
        }*/
        
        if(count($videoFeed) < 1){
            throw new \LengthException("No results were returned");
        }

        return $this->getSimpleXml($videoFeed);
        
                
    }
    
    private function getVideoFeed(MediaSelection $mediaSelection){
        $categories = 'Film|Entertainment';
        $query = $this->youTube->newVideoQuery();
        
        //$query->setOrderBy('viewCount');
        //default ordering is relevance
        //$query->setSafeSearch('none'); //not supported when using setCategory
        $query->setMaxResults('25');
        
        switch($mediaSelection->getMediaTypes()->getSlug()){
            case 'film':
            case 'tv':
                $categories = 'Film|Entertainment';
                break;
            
        }
        $keywordQuery = Utilities::formatSearchString(array(
            'keywords'  => $mediaSelection->getComputedKeywords(),
            'media'     => $mediaSelection->getMediaTypes()->getSlug(),
        ));
        
        //$keywordQuery = urlencode($keywordQuery);
        $query->setVideoQuery($keywordQuery);
        $query->setCategory(urlencode($categories));
        $this->query = $query->getQueryUrl(2);
        
        return $this->youTube->getVideoFeed($query->getQueryUrl(2));    
                
    }
    
    private function getSimpleXml($videoFeed){
        $sxml = new SimpleXMLElement('<feed>');
        //$feed = $sxml->addChild('feed');
        foreach($videoFeed as $videoEntry){
            $entry = $sxml->addChild('entry');
            $this->constructVideoEntry($entry, $videoEntry);
        }
        
        //debug - output the search url
        $url = $sxml->addChild('url');
        $url[0] = $this->query;
        return $sxml;
    }
    
    private function constructVideoEntry(SimpleXMLElement $entry, $videoEntry){
        $id = $entry->addChild('id');
        $id[0] = $videoEntry->getVideoId();

        $thumbnails = $videoEntry->getVideoThumbnails();
        $tn = end($thumbnails);

        $thumbnail = $entry->addChild('thumbnail');
        $thumbnail[0] = $tn['url'];

        $title = $entry->addChild('title');
        $title[0] = $videoEntry->getVideoTitle();
        
        return $entry;
    }
    
   
}

?>
