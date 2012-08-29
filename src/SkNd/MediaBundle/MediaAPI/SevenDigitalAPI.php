<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Connects to 7Digital API to return music
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\MediaAPI;
require_once 'SimpleXmlRequest.php';
use SkNd\MediaBundle\Entity\MediaSelection;
use \SimpleXMLElement;

class SevenDigitalAPI implements IAPIStrategy{
    const API_NAME = 'sevendigital';
    const FRIENDLY_NAME = '7Digital';
    protected $method = "release/bytag/top";
    protected $host = "http://api.7digital.com/1.2/";
    protected $page = 1;
    protected $pageSize = 50;
    protected $apiKey = "YOUR_KEY_HERE";
    //protected $decade;
    //protected $genre;
    protected $tags;
    
    /*
     * get the request by passing genre and decade tags to 7Digital
     * which results in a simplexml response which can be passed to the template
     */
    public function getRequest(array $params){
        
        ksort($params);
        $params = implode(",", $params);

        $request = $this->host . $this->method . "?tags=" . $params . "&page=". $this->page . "&pageSize=" . $this->pageSize . "&oauth_consumer_key=" . $this->apiKey;
        return getSimpleXmlResponse($request);    
    }
    
    public function getName(){
        return self::API_NAME;
    }
    
    /*
     * for youtube, details are retrieved on the client,
     * but still need to be stored to drive recommendations, timeline
     * and improve memory walls
     */
    public function getDetails(array $params){}
    public function getBatch(array $ids){}
    public function getBatchProcessThreshold() {}
    
    public function getId(SimpleXMLElement $xmlData){}
    public function getIdFromXML(SimpleXMLElement $xmlData);
    public function getImageUrlFromXML(SimpleXMLElement $xmlData) {}
    public function getItemTitleFromXML(SimpleXMLElement $xmlData){}
    public function getListings(MediaSelection $mediaSelection){}
    public function getValidCreationTime(){}
    public function getXML(SimpleXMLElement $xmlData);
}


?>
