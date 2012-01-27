<?php
/*
 * Copyright (c) 2011 Simon Kerr
 * Connects to YouTube api to return results for all media
 * @author Simon Kerr
 * @version 1.0
 */
namespace ThinkBack\MediaBundle\MediaAPI;
require_once 'Zend/Loader.php';

class YouTubeAPI extends MediaAPI {
    protected $youTube;
    private $query;
    
    public function __construct($youtube_request_object = null){
        //get access to the youtube methods
        \Zend_Loader::loadClass('Zend_Gdata_YouTube');
        
        //$this->youTube = $youtube_request_object == null ? new \Zend_Gdata_YouTube() : new $youtube_request_object;
        $this->youTube = $youtube_request_object == null ? new \Zend_Gdata_YouTube() : $youtube_request_object;
       
    }
    
    public function getRequest(array $params){
        $this->youTube->setMajorProtocolVersion(2);
        
                
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
                
        $videoFeed = $this->getVideoFeed($params);

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
    
    private function getVideoFeed($params){
        $query = $this->youTube->newVideoQuery();
        
        //$query->setOrderBy('viewCount');
        //default ordering is relevance
        //$query->setSafeSearch('none'); //not supported when using setCategory
        $query->setMaxResults('25');
        
        switch($params['media']){
            case 'film':
            case 'tv':
                $categories = 'Film|Entertainment';
                break;
            
        }
        $keywordQuery = parent::formatSearchString($params);
        
        //$keywordQuery = urlencode($keywordQuery);
        $query->setVideoQuery($keywordQuery);
        $query->setCategory(urlencode($categories));
        $this->query = $query->getQueryUrl(2);
        return $this->youTube->getVideoFeed($query->getQueryUrl(2));    
                
    }
    
    private function getSimpleXml($videoFeed){
        $sxml = new \SimpleXMLElement('<xml></xml>');
        $feed = $sxml->addChild('feed');
        foreach($videoFeed as $videoEntry){
            $entry = $feed->addChild('entry');
                        
            $id = $entry->addChild('id');
            $id[0] = $videoEntry->getVideoId();
            
            $thumbnails = $videoEntry->getVideoThumbnails();
            $tn = end($thumbnails);
            
            $thumbnail = $entry->addChild('thumbnail');
            $thumbnail[0] = $tn['url'];
            
            $title = $entry->addChild('title');
            $title[0] = $videoEntry->getVideoTitle();
        }
        
        //debug - output the search url
        $url = $sxml->addChild('url');
        $url[0] = $this->query;
        return $sxml;
    }
    
   
}

?>
