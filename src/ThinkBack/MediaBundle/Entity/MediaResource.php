<?php

namespace ThinkBack\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var string $title
     */
    protected $title;

    /**
     * @var string $thumbnailUrl
     */
    protected $thumbnailUrl;

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
    
    protected $mediaType;
    protected $mediaType_id;
    
    /*protected $genre;
    protected $genre_id;
    
    protected $decade;
    protected $decade_id;*/
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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

    
    public function setName($name)
    {
        $this->name = $name;
    }

   
    public function getName()
    {
        return $this->name;
    }
    
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }
   
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set thumbnailUrl
     *
     * @param string $thumbnailUrl
     */
    public function setThumbnailUrl($thumbnailUrl)
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    /**
     * Get thumbnailUrl
     *
     * @return string 
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
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
    
    
    public function getMediaType(){
        return $this->mediaType;
    }
    
    public function setMediaType(\ThinkBack\MediaBundle\Entity\MediaType $mediaType){
        return $this->mediaType = $mediaType;
    }
    
    public function getMediaTypeId(){
        return $this->mediaType_id;
    }
    
    public function setMediaTypeId($mediaTypeId){
        return $this->mediaType_id = $mediaTypeId;
    }
    
    
    
}