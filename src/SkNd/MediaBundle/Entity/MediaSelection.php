<?php

namespace SkNd\MediaBundle\Entity;
/**
 * SkNd\MediaBundle\Entity\MediaSelection
 */
class MediaSelection
{
    protected $decades;

    protected $mediaTypes;

    protected $genres;
    
    protected $selectedMediaGenres;

    protected $keywords;
    
    protected $page;
    
    protected $computedKeywords;
    
    public function __construct(){
        $this->page = null;
        $this->keywords = null;
    }
    
    public function setPage($page){
        $this->page = $page;
    }
    
    public function getPage(){
        return $this->page;
    }
    
    public function setSelectedMediaGenres($genres){
        $this->selectedMediaGenres = $genres;
    }
    public function getSelectedMediaGenres(){
        return $this->selectedMediaGenres;
    }

    public function setDecades($decades)
    {
        $this->decades = $decades;
    }

   
    public function getDecades()
    {
        return $this->decades;
    }

    
    public function setMediaTypes($mediaTypes)
    {
        $this->mediaTypes = $mediaTypes;
    }

   
    public function getMediaTypes()
    {
        return $this->mediaTypes;
    }

    
    public function setGenres($genres)
    {
        $this->genres = $genres;
    }

    
    public function getGenres()
    {
        return $this->genres;
    }
    
    
    public function setKeywords($keywords)
    {
        $this->keywords = urlencode($keywords);
    }

    public function getKeywords()
    {
        if($this->keywords == null)
            return null;
        else
            return urldecode($this->keywords);
    }
    
    public function setComputedKeywords($computedKeywords)
    {
        $this->computedKeywords = urlencode($computedKeywords);
    }

    public function getComputedKeywords()
    {
        if($this->computedKeywords == null)
            return null;
        else
            return urldecode($this->computedKeywords);
    }
    
}