<?php

namespace ThinkBack\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ThinkBack\MediaBundle\Entity\Genre;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\MediaType;
use ThinkBack\MediaBundle\Entity\API;

/**
 * ThinkBack\MediaBundle\Entity\RecommendedItem
 */
class MediaResource
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $itemId
     */
    protected $itemId;

    /**
     * @var integer $viewCount
     */
    protected $viewCount;

    /**
     * @var integer $selectedCount
     */
    protected $selectedCount;
    
    /**
     * @var datetime $lastUpdated
     */
    protected $lastUpdated;
    
    protected $dateCreated;
    
    protected $mediaType;
    
    protected $decade;
    
    protected $genre;
    
    protected $api;

    
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

    /**
     * Set itemId
     *
     * @param string $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * Get itemId
     *
     * @return string 
     */
    public function getItemId()
    {
        return $this->itemId;
    }

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
        
    
}