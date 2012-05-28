<?php

namespace ThinkBack\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ThinkBack\MediaBundle\Entity\Genre;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\MediaType;
use ThinkBack\MediaBundle\Entity\API;
use ThinkBack\MediaBundle\Entity\MediaResourceCache;


/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResource saves and retrieves items from any api - amazon, youtube, google images, 7digital etc,
 * checking for cached versions of details 
 * @author Simon Kerr
 * @version 1.0
 */

class MediaResource
{

    protected $id;

    //protected $itemId;

    protected $viewCount;

    protected $selectedCount;
    
    protected $lastUpdated;
    
    protected $dateCreated;
    
    protected $mediaType;
    
    protected $decade;
    
    protected $genre;
    
    protected $api;
    
    protected $mediaResourceCache;

    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setMediaResourceCache(MediaResourceCache $mediaResourceCache){
        $this->mediaResourceCache = $mediaResourceCache;
    }
    
    public function getMediaResourceCache(){
        return $this->mediaResourceCache;
    }
    
    public function setMediaType(MediaType $mediaType)
    {
        $this->mediaType = $mediaType;
    }

    public function getMediaType()
    {
        return $this->mediaType;
    }


    public function setDecade(Decade $decade)
    {
        $this->decade = $decade;
    }


    public function getDecade()
    {
        return $this->decade;
    }

    public function setGenre(Genre $genre)
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

    
    /*public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    
    public function getItemId()
    {
        return $this->itemId;
    }*/

    /**
     * Set viewCount
     *
     * @param integer $viewCount
     */
    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;
    }

    /**
     * Get viewCount
     *
     * @return integer 
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }
    
    /**
     * Set selectedCount
     *
     * @param integer $selectedCount
     */
    public function setSelectedCount($selectedCount)
    {
        $this->selectedCount = $selectedCount;
    }

    /**
     * Get selectedCount
     *
     * @return integer 
     */
    public function getSelectedCount()
    {
        return $this->selectedCount;
    }

    /**
     * Set lastUpdated
     *
     * @param datetime $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * Get lastUpdated
     *
     * @return datetime 
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }


    public function getDateCreated()
    {
        return $this->dateCreated;
    }
        
    
}