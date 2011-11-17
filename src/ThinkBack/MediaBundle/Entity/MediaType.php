<?php

namespace ThinkBack\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
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
     * @var string $amazonBrowseNodeId
     */
    protected $amazonBrowseNodeId;
    
    protected $genres;
    //protected $decades;
    
    /**
     * @var string $slug
     */
    private $slug;
    
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

    /**
     * Add genres
     *
     * @param ThinkBack\MediaBundle\Entity\Genre $genres
     */
    public function addGenre(\ThinkBack\MediaBundle\Entity\Genre $genres)
    {
        $this->genres[] = $genres;
    }
    


    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }
}