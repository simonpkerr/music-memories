<?php

namespace ThinkBack\MediaBundle\Repository;

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
        $query = $this->_em->createQuery('SELECT g FROM ThinkBack\MediaBundle\Entity\Genre g JOIN g.mediaType m WHERE g.slug = :slug AND m.slug = :media');
        $query->setParameters(
                array(
                    'slug' => $slug,
                    'media' => $media,
                    )
                );
        
        /*$query = $this->createQueryBuilder('g')
                ->select('g.amazonBrowseNodeId')
                ->join('g.mediaType', 'm', 'ON', 'm.slug = :media')
                ->where('g.slug = :slug')
                ->setParameters(array(
                    'slug' => $slug,
                    'media' => $media,))
                ->getQuery();*/
                
               //$qb = $em->createQueryBuilder() ->select('u') ->from('User', 'u') ->innerJoin('u.Phonenumbers', 'p', Expr\Join::WITH, 'p.is_primary = 1');
        //$query = $this->createQuery('SELECT g.amazonBrowseNodeId FROM Genre g WHERE g.slug == :slug AND g.mediaType_id == m.id JOIN mediaType m WITH m.slug == :media');
         
        return $query->getSingleResult();
            
    }
    
    
    
}