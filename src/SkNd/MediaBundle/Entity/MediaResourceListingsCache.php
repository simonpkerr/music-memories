<?php

namespace SkNd\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\MediaType;
use SkNd\MediaBundle\Entity\API;

/**
 * SkNd\MediaBundle\Entity\MediaResourceListingsCache
 */
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

    /**
     * @var integer $page
     */
    protected $page;

    protected $keywords;
    protected $computedKeywords;

    protected $xmlData;

    protected $dateCreated;
    
    protected $api_id;
    protected $mediaType_id;
    protected $genre_id;
    protected $decade_id;

    public function getMediaType_id(){
        return $this->mediaType_id;
    }
    public function getApi_id(){
        return $this->api_id;
    }
    public function getGenre_id(){
        return $this->genre_id;
    }
    public function getDecade_id(){
        return $this->decade_id;
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
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
    public function setXmlData($xmlData)
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
        return $this->xmlData;
    }

    /**
     * Set dateCreated
     *
     * @param datetime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * Get dateCreated
     *
     * @return datetime 
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}