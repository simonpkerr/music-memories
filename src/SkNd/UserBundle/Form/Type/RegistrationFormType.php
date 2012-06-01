<?php
namespace SkNd\UserBundle\Form\Type {
    
use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType {
    
    public function buildForm(FormBuilder $builder, array $options){
        parent::buildForm($builder, $options);
        
        //any extra properties are added here
        $builder->add('firstname', 'text', array(
            'required'  => false,
        ));
        $builder->add('surname', 'text', array(
            'required'  => false,
        ));
        $startYear = new \DateTime("now");
        $startYear = $startYear->sub(date_interval_create_from_date_string('90 years'));
        $yearRange = range($startYear->format('Y'), date('Y'));
        arsort($yearRange);
        $builder->add('dateofbirth', 'birthday', array(
            'years' => $yearRange,
            'label' => 'Date of Birth',
            ));
        
    }
    
    public function getName(){
        return 'sk_nd_user_registration';
    }
    
}
    
}


?>
