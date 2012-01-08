<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * @author Simon Kerr
 * @version 1.0
 * MediaSelectionType used to make the media selection widget generic for
 * re-use purposes
 * 
 */

namespace ThinkBack\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class MediaSelectionType extends AbstractType{
    
    public function buildForm(FormBuilder $builder, array $options = null){
        
        //entity field mapped to the decade class displaying the id and decadeName properties
        $builder->add('decades', 'entity', array(
            'label'         => 'Decade',
            'property'      => 'decadeName',
            'class'         => 'ThinkBackMediaBundle:Decade',
            'empty_value'   => 'All Decades',
            'required'      => false,
            //'multiple'  =>  true,
            
        ));
        
        //entity field mapped to the mediatype class displaying the id and mediaName properties
        $builder->add('mediaTypes', 'entity', array(
            'property'      => 'mediaName',
            'label'         => 'Media',
            'class'         => 'ThinkBackMediaBundle:MediaType',
        ));
        
        $builder->add('selectedMediaGenres','entity', array(
            'label'             =>  'Genre',
            'property'          =>  'genreName',
            'class'             =>  'ThinkBackMediaBundle:Genre',
            'empty_value'       =>  'All Genres',
            'required'          =>  false,
            'query_builder'     =>  function(EntityRepository $er){
                return $er->createQueryBuilder('g')
                        ->orderBy('g.genreName', 'ASC');
            },                    
        ));
        
        $builder->add('keywords', 'text', array(
            'label'     => 'Keywords (optional)',
            'required'  => false,
            'trim'      => true,
   
        ));
        /*$builder->add('genres','entity', array(
            'label'             =>  'Genre',
            'property'          =>  'genreName',
            'class'             =>  'ThinkBackMediaBundle:Genre',
            'query_builder'     =>  function(EntityRepository $er){
                return $er->createQueryBuilder('g')
                        ->orderBy('g.genreName', 'ASC');
            },                    
        ));*/
        
        //hidden field to hold all genres 
        $builder->add('genres', 'hidden');
 
                
    }
    
    public function getName(){
        return 'mediaSelection';
    }
    
    public function getDefaultOptions(array $options){
        return array(
          'data_class' => 'ThinkBack\MediaBundle\Entity\MediaSelection',  
        );
    }
    
    
    
    
}

?>
