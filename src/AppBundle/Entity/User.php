<?php
  
  namespace AppBundle\Entity;
  
  use Symfony\Component\Security\Core\User\UserInterface;
  use Doctrine\ORM\Mapping as ORM;
  
  /**
   * AppBundle\Entity\User
   * 
   * @ORM\Table(name="users")
   * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
   */  
  
  class User implements UserInterface, \Serializable
  {
      /**
       * @ORM\Column(type="integer")
       * @ORM\Id
       * @ORM\GeneratedValue(strategy="AUTO")
       */
      private $id;
      
      /**
       * @ORM\Column(type="string", length=50, unique=true)
       */
      private $email;
      
      /**
       * @ORM\Column(type="string", length=100)
       */
      private $password;
      
      /**
       * @ORM\Column(name="is_active", type="boolean")
       */
      private $is_active;
  }
    
?>