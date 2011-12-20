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
    //private $host = 'http://gdata.youtube.com';
    
    public function __construct($container = null){
        if($container != null)
            parent::__construct($container);
        
        //get access to the youtube methods
        \Zend_Loader::loadClass('Zend_Gdata_YouTube');
        
    }
    
    public function getRequest(array $params){
        $yt = new \Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);
        $query = $yt->newVideoQuery();
        //$query->setOrderBy('viewCount');
        //default ordering is relevance
        //$query->setSafeSearch('none');
        $keywordQuery = strtolower($params['keywords']);
        //$keywordQuery = rawurlencode(str_replace("-", "", $keywordQuery));
        $keywordQuery = trim(substr($keywordQuery, 0, strrpos($keywordQuery, "-")));
        $query->setVideoQuery($keywordQuery);
        
        //$query->setCategory("Film/Entertainment/" . $keywordQuery);
        /*$searchTermsArray =  $params['keywords']
        foreach ($searchTermsArray as $searchTerm) {
          $keywordQuery .= strtolower($searchTerm) . '/';
        }*/
        //$query->setCategory($keywordQuery);
        
        $videoFeed = $yt->getVideoFeed($query->getQueryUrl(2));    
        return $this->getSimpleXml($videoFeed, $query);
        //$videoFeed = @simplexml_load_file($query->getQueryUrl(2)); 
        
        if($videoFeed === false)
            throw new \RuntimeException("Could not connect to YouTube");
                
        return $videoFeed;
    }
    
    private function getSimpleXml($videoFeed, $query){
        $sxml = new \SimpleXMLElement('<xml></xml>');
        $feed = $sxml->addChild('feed');
        foreach($videoFeed as $videoEntry){
            $entry = $feed->addChild('entry');
            $thumbnails = $videoEntry->getVideoThumbnails();
            $tn = end($thumbnails);
            $tnurl = $tn['url'];
            $entry->addChild('id', $videoEntry->getVideoId());
            $entry->addChild('thumbnail', $tnurl);
            $entry->addChild('title', str_replace("&", "&amp;", $videoEntry->getVideoTitle()));            
        }
        
        //debug
        $sxml->addChild('url', $query->getQueryUrl(2));
        return $sxml;
    }
    
}

?>
