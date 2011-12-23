<?php
/*
 * Copyright (c) 2011 Simon Kerr
 * Connects to YouTube api to return results for all media
 * @author Simon Kerr
 * @version 1.0
 */
namespace ThinkBack\MediaBundle\Resources\MediaAPI;
require_once 'Zend/Loader.php';

class YouTubeAPI extends MediaAPI {
    protected $youTube;
    
    public function __construct($container = null, $yt = null){
        if($container != null)
            parent::__construct($container);
        
        $this->youTube = $yt == null ? new \Zend_Gdata_YouTube() : $yt;
        
        //get access to the youtube methods
        \Zend_Loader::loadClass('Zend_Gdata_YouTube');
        
    }
    
    public function getRequest(array $params){
        $this->youTube->setMajorProtocolVersion(2);
        $query = $this->youTube->newVideoQuery();
        
        //$query->setOrderBy('viewCount');
        //default ordering is relevance
        //$query->setSafeSearch('none'); //not supported when using setCategory
        $query->setMaxResults('25');
        $keywordQuery = parent::formatSearchString($params);
        
        //------to send a simple query to youtube       
        //$query->setVideoQuery($keywordQuery);
        
        //------to set up a category and keyword search
        switch($params['media']){
            case 'film':
            case 'tv':
                $categories = 'Film|Entertainment';
                break;
            /*case 'tv':
                $categories = 'Entertainment';
            break;*/
        }
        /*$categories = '/Music|';
        if($params['media'] == 'film')
            $categories .= 'Film|Entertainment';
        else
            $categories .= '|Entertainment';
       */
        
        //$keywordQuery = str_replace(' ', '/', $keywordQuery) . $categories;
        //$query->setCategory($keywordQuery);
        
        //$keywordQuery = urlencode($keywordQuery);
        $query->setVideoQuery($keywordQuery);
        $query->setCategory(urlencode($categories));
        
        //$query->setCategory("Film/Entertainment/" . $keywordQuery);
        /*$searchTermsArray =  $params['keywords']
        foreach ($searchTermsArray as $searchTerm) {
          $keywordQuery .= strtolower($searchTerm) . '/';
        }*/
        //$query->setCategory($keywordQuery);
        
        $videoFeed = $this->youTube->getVideoFeed($query->getQueryUrl(2));    

        if($videoFeed === false)
            throw new \RuntimeException("Could not connect to YouTube");
        
        if(count($videoFeed) < 1){
            throw new \LengthException("No results were returned");
        }
        
        return $this->getSimpleXml($videoFeed, $query);
    }
    
    private function getSimpleXml($videoFeed, $query){
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
        $url[0] = $query->getQueryUrl(2);
        return $sxml;
    }
    
   
}

?>
