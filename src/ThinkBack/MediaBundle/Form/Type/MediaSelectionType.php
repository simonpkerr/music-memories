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
        
        /*if($options['data']->getMediaTypes() != null){
            $mediaSelection = $options['data'];
            $id = $options['data']->getMediaTypes()->getId();
            $builder->add('selectedMediaGenres','entity', array(
                'label'             =>  'Genre',
                'property'          =>  'genreName',
                'class'             =>  'ThinkBackMediaBundle:Genre',
                'query_builder'     =>  function(EntityRepository $er) use ($id){
                    return $er->createQueryBuilder('g')
                            ->where('g.mediaType_id = :mediaType')
                            ->setParameter('mediaType', $id)
                            ->orderBy('g.genreName', 'ASC');
                },                    
            ));
        }else{*/
        
        /*
         * initially was going to just load the genres related to the 
         * media that was selected but this causes an error so all genres
         * are loaded here, the correct one from the submission is selected
         * and jquery is used to trim the select and only show the correct genres
         * for the selected media
         */
        $builder->add('selectedMediaGenres','entity', array(
            'label'             =>  'Genre',
            'property'          =>  'genreName',
            'class'             =>  'ThinkBackMediaBundle:Genre',
            'query_builder'     =>  function(EntityRepository $er){
                return $er->createQueryBuilder('g')
                        ->orderBy('g.genreName', 'ASC');
            },                    
        ));
 
                
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
