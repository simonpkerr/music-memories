<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaSelection gets and sets the currently selected media, decade, genre, keywords and page number
 * checking for cached versions of details 
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Entity;
class MediaSelection
{
    protected $api; 
    
    protected $decade;

    protected $mediaType;

    protected $genres;
    
    protected $selectedMediaGenre;

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
    
    public function setAPI($api){
        $this->api = $api;
    }
    
    public function getAPI(){
        return $this->api;
    }
    
    public function setSelectedMediaGenre($genre){
        $this->selectedMediaGenre = $genre;
    }
    public function getSelectedMediaGenre(){
        return $this->selectedMediaGenre;
    }

    public function setDecade($decade)
    {
        $this->decade = $decade;
    }

   
    public function getDecade()
    {
        return $this->decade;
    }

    
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;
    }

   
    public function getMediaType()
    {
        return $this->mediaType;
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