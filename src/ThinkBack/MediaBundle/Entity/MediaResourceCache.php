<?php

namespace ThinkBack\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ThinkBack\MediaBundle\Entity\MediaResourceCache
 */
class MediaResourceCache
{
    /**
     * @var integer $id
     */
    private $id;

    //private $mediaResourceId;

    /**
     * @var text $xmlData
     */
    private $xmlData;

    /**
     * @var datetime $dateCreated
     */
    private $dateCreated;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var string $imageUrl
     */
    private $imageUrl;


    
    public function setId($id){
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }

    
    /*public function setMediaResourceId($mediaResourceId)
    {
        $this->mediaResourceId = $mediaResourceId;
    }

    
    public function getMediaResourceId()
    {
        return $this->mediaResourceId;
    }*/

    /**
     * Set xmlData
     *
     * @param text $xmlData
     */
    public function setXmlData($xmlData)
    {
        $this->xmlData = $xmlData;
    }

    /**
     * Get xmlData
     *
     * @return text 
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

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug = null)
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

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title = null)
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
     * Set imageUrl
     *
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl = null)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * Get imageUrl
     *
     * @return string 
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }
}