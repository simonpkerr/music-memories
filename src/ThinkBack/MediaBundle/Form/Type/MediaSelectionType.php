<?php

namespace ThinkBack\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

/*
 * Class is used to make the media selection widget generic for
 * re-use purposes
 */
class MediaSelectionType extends AbstractType{
    
    public function buildForm(FormBuilder $builder, array $options = null){
        
        //entity field mapped to the decade class displaying the id and decadeName properties
        $builder->add('decades', 'entity', array(
            'label'     => 'Decade',
            'property'  => 'decadeName',
            'class'     => 'ThinkBackMediaBundle:Decade',
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
            'query_builder'     =>  function(EntityRepository $er){
                return $er->createQueryBuilder('g')
                        ->orderBy('g.genreName', 'ASC');
            },                    
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
