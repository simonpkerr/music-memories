<?php
namespace ThinkBack\UserBundle\Entity {
    
    use FOS\UserBundle\Entity\User as BaseUser;
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class User extends BaseUser {

        protected $id;

        protected $firstname;

        protected $surname;

        protected $dateofbirth;
        
        public function __construct(){
            parent::__construct();
        }
        

        /**
         * Get id
         *
         * @return integer 
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set firstname
         *
         * @param string $firstname
         */
        public function setFirstname($firstname)
        {
            $this->firstname = $firstname;
        }

        /**
         * Get firstname
         *
         * @return string 
         */
        public function getFirstname()
        {
            return $this->firstname;
        }

        /**
         * Set surname
         *
         * @param string $surname
         */
        public function setSurname($surname)
        {
            $this->surname = $surname;
        }

        /**
         * Get surname
         *
         * @return string 
         */
        public function getSurname()
        {
            return $this->surname;
        }

        /**
         * Set dateofbirth
         *
         * @param date $dateofbirth
         */
        public function setDateofbirth($dateofbirth)
        {
            $this->dateofbirth = $dateofbirth;
        }

        /**
         * Get dateofbirth
         *
         * @return date 
         */
        public function getDateofbirth()
        {
            return $this->dateofbirth;
        }
    }
}
