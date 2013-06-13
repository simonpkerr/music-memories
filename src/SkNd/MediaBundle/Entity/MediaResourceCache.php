<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResourceCache is responsible for saving actual media resource data including name, xmldata, image etc
 * checking for cached versions of details 
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Entity;

use SkNd\MediaBundle\MediaAPI\MediaAPI;

class MediaResourceCache
{
    private $id;
    private $xmlData;
    private $dateCreated;
    private $slug;
    private $title;
    private $imageUrl;
    protected $xmlRef;
    
    public function setId($id){
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }
  
    public function setXmlData($xmlData)
    {
        $this->xmlData = $xmlData;
    }
    
    public function getRawXmlData(){
        return simplexml_load_string($this->xmlData);
    }
 
    public function getXmlData()
    {
        $f = MediaAPI::CACHE_PATH . $this->getXmlRef() . '.xml';
        if(file_exists($f)){
            try{
                $this->setXmlData(simplexml_load_file($f));
            }catch(\Exception $e) {
                throw new \Exception("error loading details");
            }
        } else {
            throw new \RuntimeException ("error loading details - file does not exist");
        }
        
        return $this->xmlData;
        
    }
    
    public function deleteXmlRef(){
        //unlink cached xml file
        $f = MediaAPI::CACHE_PATH . $this->getMediaResourceCache()->getXmlRef() . '.xml';
        if(file_exists($f)){
            try {
                unlink($f);
            } catch(\Exception $e){
                throw new \Exception("error deleting cached media resource");
            }
        } 
    }
    
    public function setXmlRef($xmlRef)
    {
        $this->xmlRef = $xmlRef;
    }
 
    public function getXmlRef()
    {
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