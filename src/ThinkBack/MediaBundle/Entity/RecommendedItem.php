<?php

namespace ThinkBack\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ThinkBack\MediaBundle\Entity\RecommendedItem
 */
class RecommendedItem
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
     * @var string $userTitle
     */
    protected $userTitle;

    /**
     * @var string $userDescription
     */
    protected $userDescription;

    /**
     * @var integer $viewCount
     */
    protected $viewCount;

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

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
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
     * Set userTitle
     *
     * @param string $userTitle
     */
    public function setUserTitle($userTitle)
    {
        $this->userTitle = $userTitle;
    }

    /**
     * Get userTitle
     *
     * @return string 
     */
    public function getUserTitle()
    {
        return $this->userTitle;
    }

    /**
     * Set userDescription
     *
     * @param string $userDescription
     */
    public function setUserDescription($userDescription)
    {
        $this->userDescription = $userDescription;
    }

    /**
     * Get userDescription
     *
     * @return string 
     */
    public function getUserDescription()
    {
        return $this->userDescription;
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