<?php

/*
 * Original code Copyright (c) 2012 Simon Kerr
 * MediaTypeRepository gets media types from the db based on slug or gets all
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MediaTypeRepository extends EntityRepository
{
    public function getMediaTypes(){
        $mediaTypes = $this->findAll();            
                
        return $mediaTypes;                
    }
    
    public function getMediaTypeBySlug($slug){
        return $this->findOneBy(array('slug' => $slug));
    }
    
    public function getDefaultMediaType(){
        return $this->findOneBy(array('slug' => 'film-and-tv'));
    }
    
}