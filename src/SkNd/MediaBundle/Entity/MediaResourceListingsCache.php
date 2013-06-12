<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResourceListingsCache is responsible for getting and setting cached listings for amazon, youtube and other apis
 * checking for cached versions of details 
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\MediaType;
use SkNd\MediaBundle\Entity\API;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use \SimpleXMLElement;

class MediaResourceListingsCache
{
    /**
     * @var integer $id
     */
    protected $id;

    protected $mediaType;

    protected $decade;

    protected $genre;
    
    protected $api;

    protected $page;

    protected $keywords;
    
    protected $computedKeywords;

    private $xmlData;

    protected $xmlRef;
    
    protected $dateCreated;
    
    protected $lastModified;
    
    public function getId()
    {
        return $this->id;
    }


    public function setMediaType(MediaType $mediaType)
    {
        $this->mediaType = $mediaType;
    }

    public function getMediaType()
    {
        return $this->mediaType;
    }


    public function setDecade(Decade $decade = null)
    {
        $this->decade = $decade;
    }


    public function getDecade()
    {
        return $this->decade;
    }

    public function setGenre(Genre $genre = null)
    {
        $this->genre = $genre;
    }

    public function getGenre()
    {
        return $this->genre;
    }
    
    public function setAPI(API $api)
    {
        $this->api = $api;
    }

    public function getAPI()
    {
        return $this->api;
    }

    /**
     * Set page
     *
     * @param integer $page
     */
    public function setPage($page = null)
    {
        $this->page = $page;
    }

    /**
     * Get page
     *
     * @return integer 
     */
    public function getPage()
    {
        return $this->page;
    }

    public function setKeywords($keywords = null)
    {
        $this->keywords = $keywords;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }
    
    public function setComputedKeywords($computedKeywords = null)
    {
        $this->computedKeywords = $computedKeywords;
    }

    public function getComputedKeywords()
    {
        return $this->computedKeywords;
    }

    /**
     * Set xmlData
     *
     * @param object $xmlData
     */
    public function setXmlData($xmlData = null)
    {
        $this->xmlData = $xmlData;
    }

    /**
     * Get xmlData
     *
     * @return object 
     */
    public function getXmlData()
    {
        try{
            $this->setXmlData(simplexml_load_file(MediaAPI::CACHE_PATH . $this->getXmlRef() . '.xml'));
        }catch(\Exception $e) {
            throw new \Exception("error loading listings");
        }
        
        return $this->xmlData;
    }

    public function setXmlRef($xmlRef = null){
        $this->xmlRef = $xmlRef;
    }
    
    public function getXmlRef(){
        return $this->xmlRef;
    }
    
    
    
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    
    public function getLastModified()
    {
        return $this->lastModified;
    }
    
    public function urlize($title){
        return Util\Urlizer::urlize((string)$title);
    }
}