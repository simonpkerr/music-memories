<?php

namespace ThinkBack\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ThinkBack\MediaBundle\Entity\MediaType
 */
class MediaType
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $mediaName
     */
    protected $mediaName;

    /**
     * @var string $mediaNameSlug
     */
    protected $mediaNameSlug;

    /**
     * @var string $amazonBrowseNodeId
     */
    protected $amazonBrowseNodeId;
    
    protected $genres;
    //protected $decades;
    
    public function __construct(){
        $this->genres = new ArrayCollection();
        //$this->decades = new ArrayCollection();
    }
    
    public function getGenres(){
        return $this->genres;
    }
    
    /*public function getDecades(){
        return $this->decades;
    }*/

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
     * Set mediaName
     *
     * @param string $mediaName
     */
    public function setMediaName($mediaName)
    {
        $this->mediaName = $mediaName;
    }

    /**
     * Get mediaName
     *
     * @return string 
     */
    public function getMediaName()
    {
        return $this->mediaName;
    }

    /**
     * Set mediaNameSlug
     *
     * @param string $mediaNameSlug
     */
    public function setMediaNameSlug($mediaNameSlug)
    {
        $this->mediaNameSlug = $mediaNameSlug;
    }

    /**
     * Get mediaNameSlug
     *
     * @return string 
     */
    public function getMediaNameSlug()
    {
        return $this->mediaNameSlug;
    }

    /**
     * Set amazonBrowseNodeId
     *
     * @param string $amazonBrowseNodeId
     */
    public function setAmazonBrowseNodeId($amazonBrowseNodeId)
    {
        $this->amazonBrowseNodeId = $amazonBrowseNodeId;
    }

    /**
     * Get amazonBrowseNodeId
     *
     * @return string 
     */
    public function getAmazonBrowseNodeId()
    {
        return $this->amazonBrowseNodeId;
    }
}