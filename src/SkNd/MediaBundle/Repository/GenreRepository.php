<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * GenreRepository gets all genres, or by slug or returns them as json data for use by the front end scripts
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GenreRepository extends EntityRepository
{
    
    public function getAllGenres(){
        $genres = $this->createQueryBuilder('g')
                ->select(array('g.id', 'g.genreName', 'g.mediaType_id'))
                ->orderBy('g.genreName')
                ->getQuery()
                ->getResult();
        
        return json_encode($genres);  
    }
    
    public function getGenresByMediaType($mediaTypeId){
        $genres = $this->createQueryBuilder('g')
                ->select(array('g.id', 'g.genreName', 'g.mediaType_id'))
                ->where('g.mediaType_id == :mediaType')
                ->setParameter('mediaType', $mediaTypeId)
                ->getQuery()
                ->getResult();
        
        return $genres;
    }
    
    public function getGenreBySlug($slug){
        return $this->findOneBy(array('slug' => $slug));
    }
    
    public function getGenreBySlugAndMedia($slug, $media){
        $query = $this->_em->createQuery('SELECT g FROM SkNd\MediaBundle\Entity\Genre g JOIN g.mediaType m WHERE g.slug = :slug AND m.slug = :media');
        $query->setParameters(
                array(
                    'slug' => $slug,
                    'media' => $media,
                    )
                );
        
        
        return $query->getSingleResult();
            
    }
    
    
    
}