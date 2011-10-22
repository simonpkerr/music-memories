<?php
namespace ThinkBack\UserBundle\Form\Type {
    
use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType {
    
    public function buildForm(FormBuilder $builder, array $options){
        parent::buildForm($builder, $options);
        
        //any extra properties are added here
        $builder->add('firstname', 'text');
        $builder->add('surname', 'text');
        $builder->add('dateofbirth', 'birthday', array(
            'years' => range(1920, date('Y')),
            'label' => 'Date of Birth',
            ));
        
    }
    
    public function getName(){
        return 'ThinkBack_user_registration';
    }
}
    
}


?>
