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
        $builder->add('searchKeywords', 'text', array(
            'label'     => 'Search Keywords',
            'required'  => true,
            'trim'      => true,
   
        ));
        
                       
    }
    
    public function getName(){
        return 'mediaSearch';
    }
    
    public function getDefaultOptions(array $options){
        return array(
          'data_class' => 'ThinkBack\MediaBundle\Entity\MediaSearch',  
        );
    }
    
    
    
    
}

?>
