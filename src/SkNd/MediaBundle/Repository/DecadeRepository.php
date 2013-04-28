<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * DecadeRepository gets decades from the db by slug or returns all decades
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DecadeRepository extends EntityRepository
{
    public function getDecades(){
        $decades = $this->findAll();
        return $decades;
    }
    
    public function getDecadeBySlug($slug){
        return $this->findOneBy(array('slug' => $slug));
        
    }
    
    public function getDefaultDecade(){
        return $this->findOneBy(array('slug' => '2000'));
    }
    
}