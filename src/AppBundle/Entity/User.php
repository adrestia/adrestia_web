<?php
  
  namespace AppBundle\Entity;
  
  use Symfony\Component\Security\Core\User\UserInterface;
  use Doctrine\ORM\Mapping as ORM;
  use Gedmo\Mapping\Annotation as Gedmo;
  
  /**
   * AppBundle\Entity\User
   * 
   * @ORM\Table(name="users")
   * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
   */  
  
  class User implements UserInterface, \Serializable
  {
      /**
       * @ORM\Column(name="id", type="integer")
       * @ORM\Id
       * @ORM\GeneratedValue(strategy="AUTO")
       */
      private $id;
      
      /**
       * @ORM\Column(name="email", type="string", length=50, unique=true)
       */
      private $email;
      
      /**
       * @ORM\Column(name="email_confirmed", type="boolean") 
       */
      private $email_confirmed = false;
      
      /**
       * @ORM\Column(type="string", length=100)
       */
      private $password;
      
      /**
       * @ORM\Column(name="is_active", type="boolean")
       */
      private $is_active;
      
      /**
       * @ORM\Column(name="upvotes", type="integer")
       */
      private $upvotes = 0;
      
      /**
       * @ORM\Column(name="downvotes", type="integer")
       */
      private $downvotes = 0;
      
      /**
       * @ORM\Column(name="account_status", type="integer")
       *
       * Types of account_status:
       *    0 – Normal account; Full privileges
       *    1 – Suspended for 24 hours
       *    2 – Suspended for 7 days
       *    3 – Deactivated by admin
       *    4 – Deleted by user
       */
      private $account_status = 0;
      
      /**
       * @ORM\Column(name="suspended", type="boolean")
       */
      private $suspended = false;
      
      /**
       * @var \Datetime $suspended_date
       *
       * @ORM\Column(name="suspended_date", type="datetime")
       *
       * The date for when the user is able to access their account again
       */
      private $suspended_date;
      
      /** 
       * @ORM\Column(name="reports", type="integer")
       */
      private $reports;
      
      /**
       * @OneToMany(targetEntity="Post", mappedBy="user")
       */
      private $posts;
      
      /**
       * @OneToMany(targetEntity="Like", mappedBy="user")
       *
       * This includes dislikes
       */
      private $likes;
      
      /**
       * @OneToMany(targetEntity="Comment", mappedBy="user")
       */
      private $comments;
      
      /**
       * @var \Datetime $created
       *
       * @Gedmo\Timestampable(on="create")
       * @ORM\Column(type="datetime")
       */
      private $created;
      
      /**
       * @var \DateTime $updated
       *
       * @Gedmo\Timestampable(on="update")
       * @ORM\Column(type="datetime")
       */
      private $updated;
      
      /**
       * @var \DateTime $account_changed
       *
       * @ORM\Column(name="account_changed", type="datetime", nullable=true)
       * @Gedmo\Timestampable(on="change", field={"email", "password"})
       */
      private $account_canged;
      
  }
?>
