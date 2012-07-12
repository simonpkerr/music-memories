<?php

namespace SkNd\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SkNd\MediaBundle\Entity\MediaResourceCache
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

    //private $dateUpdated;
    
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

    /*public function __construct(){
        $this->dateCreated = new \DateTime("now");
                
    }*/
    
  
    public function setXmlData($xmlData)
    {
        $this->xmlData = $xmlData;
    }

   
    public function getXmlData()
    {
        return simplexml_load_string($this->xmlData);
    }

    
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function setSlug($slug = null)
    {
        $this->slug = $slug;
    }


    public function getSlug()
    {
        return $this->slug;
    }

    public function setTitle($title = null)
    {
        $this->title = $title;
    }


    public function getTitle()
    {
        return $this->title;
    }


    public function setImageUrl($imageUrl = null)
    {
        $this->imageUrl = $imageUrl;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }
}