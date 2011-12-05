<?php

namespace ThinkBack\MediaBundle\Entity;
/**
 * ThinkBack\MediaBundle\Entity\MediaSelection
 */
class MediaSelection
{
    protected $decades;

    //protected $mediaTypes;

    protected $genres;
    
   // protected $selectedMediaGenres;

    
   /* public function setSelectedMediaGenres($genres){
        $this->selectedMediaGenres = $genres;
    }
    public function getSelectedMediaGenres(){
        return $this->selectedMediaGenres;
    }*/

    public function setDecades($decades)
    {
        $this->decades = $decades;
    }

   
    public function getDecades()
    {
        return $this->decades;
    }

    
    /*public function setMediaTypes($mediaTypes)
    {
        $this->mediaTypes = $mediaTypes;
    }

   
    public function getMediaTypes()
    {
        return $this->mediaTypes;
    }*/

    
    public function setGenres($genres)
    {
        $this->genres = $genres;
    }

    
    public function getGenres()
    {
        return $this->genres;
    }
}