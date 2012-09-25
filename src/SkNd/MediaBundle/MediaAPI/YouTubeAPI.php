<?php
/*
 * Copyright (c) 2011 Simon Kerr
 * Connects to YouTube api to return results for all media,
 * handles getting listings, details and batch processing of YouTube data
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\MediaSelection;
use Doctrine\ORM\EntityManager;
use \SimpleXMLElement;

class YouTubeAPI implements IAPIStrategy {
    const FRIENDLY_NAME = 'YouTube';
    const API_NAME = 'youtubeapi';
    const BATCH_PROCESS_THRESHOLD = 24;
    
    protected $youTube;
    private $query;
    
    public function __construct($youtube_request_object = null){
        $this->youTube = $youtube_request_object == null ? new \Zend_Gdata_YouTube() : $youtube_request_object;
        $this->youTube->setMajorProtocolVersion(2);
       
    }
    
    public function getName(){
        return self::API_NAME;
    }
    
    public function setRequestObject($obj){
        $this->youTube = $obj;
    }
    
    public function getIdFromXML(SimpleXMLElement $xmlData){
        return (string)$xmlData->id;
    }
    
    public function getXML(SimpleXMLElement $xmlData){
        return $xmlData->asXML();
    }
    
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
    
    public function getBatch(array $ids){
        if(count($ids) > self::BATCH_PROCESS_THRESHOLD)
            $ids = array_slice ($ids, 0, self::BATCH_PROCESS_THRESHOLD);
        
        $feed = '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/"
xmlns:batch="http://schemas.google.com/gdata/batch" xmlns:yt="http://gdata.youtube.com/schemas/2007"><batch:operation type="query" />';
        
        $entries = array();
        foreach($ids as $id){
            array_push($entries, '<entry><id>http://gdata.youtube.com/feeds/api/videos/' . $id . '</id></entry>');
        }
        $feed = $feed . implode('', $entries) . '</feed>';
        try{
            $response = $this->youTube->post($feed, 'http://gdata.youtube.com/feeds/api/videos/batch');
        }catch(\Exception $ex){
            throw new \RuntimeException('A problem occurred connecting to YouTube');
        }
        
        if($response === false)
            throw new \RuntimeException('Could not connect to YouTube');
        
        if($response->getStatus() != 200)
            throw new \RuntimeException('A problem occurred with the response');
        
        $response = $response->getBody();//gets the raw response as Zend_Http_Response
        $feed = new \Zend_Gdata_YouTube_VideoFeed();
        $feed->setMajorProtocolVersion(2);
        try{
            $feed->transferFromXML($response);
        }catch(\Exception $ex){
            throw new \RuntimeException('Could not parse response');
        }
        $response = $this->getSimpleXml($feed);

        return $response;
        
    }
    
    public function getListings(MediaSelection $mediaSelection){
                       
        $videoFeed = $this->getVideoFeed($mediaSelection);

        if($videoFeed === false)
            throw new \RuntimeException("Could not connect to YouTube");
        
        if(count($videoFeed) < 1){
            throw new \LengthException("No results were returned");
        }

        return $this->getSimpleXml($videoFeed, true);
                
    }
    
    private function getVideoFeed(MediaSelection $mediaSelection){
        $categories = 'Entertainment';
        $query = $this->youTube->newVideoQuery();
        
        //$query->setOrderBy('viewCount');
        //default ordering is relevance
        $query->setMaxResults(self::BATCH_PROCESS_THRESHOLD);
        
        switch($mediaSelection->getMediaType()->getSlug()){
            case 'film':
                $categories = 'Film';
                break;
            case 'tv':
                $categories = 'Entertainment';
                break;
            case 'music':
                $categories = 'Music';
                break;
        }
        
        $searchString = Utilities::formatSearchString(array(
            'keywords'  => $mediaSelection->getComputedKeywords(),
            'media'     => $mediaSelection->getMediaType()->getSlug(),
            'decade'    => $mediaSelection->getDecade() != null ? $mediaSelection->getDecade()->getDecadeName() : null,
            'genre'     => $mediaSelection->getSelectedMediaGenre() != null ? $mediaSelection->getSelectedMediaGenre()->getGenreName() : null
        ));
        
        //$query->setVideoQuery(urlencode($keywordQuery));
        $query->setVideoQuery(urlencode($searchString['keywords']));
        
        //$categories .= '/' . str_replace(' ', '/', $searchString['keywords']);
        
        $query->setCategory($categories);
        $this->query = $query->getQueryUrl(2);
        
        return $this->youTube->getVideoFeed($this->query);    
                
    }
    
    private function getSimpleXml($videoFeed, $debugURL = false){
        $sxml = new SimpleXMLElement('<feed></feed>');
        //$feed = $sxml->addChild('feed');
        foreach($videoFeed as $videoEntry){
            $entry = $sxml->addChild('entry');
            $this->constructVideoEntry($entry, $videoEntry);
        }
        
        //debug - output the search url
        if($debugURL){
            $url = $sxml->addChild('url');
            $url[0] = $this->query;
        }
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
    
    /**
     * method returns a date time object against which records can be compared
     * for youtube. This enables updates to be made to out of date records
     * as there is no set time threshold for youtube, a threshold of 3 days 
     * has been chosen
     * @return type DateTime
     */
    public function getValidCreationTime(){
         $date = new \DateTime("now");
         $date = $date->sub(new \DateInterval('PT72H'))->format("Y-m-d H:i:s");

         return $date;
    }
    
   
}

?>
