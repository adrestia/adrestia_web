<?php
  
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
* AppBundle\Entity\Comment
* 
* @ORM\Table(name="comments")
*
*/  

class Comment
{
    /**
    * @ORM\Column(name="id", type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;
    
    /**
    * @ORM\Column(name="body", type="string", length=255, unique=true)
    * @Assert\NotBlank()
    */
    private $body;
    
    /**
    * @ORM\Column(name="upvotes", type="integer")
    */
    private $upvotes = 0;
    
    /**
    * @ORM\Column(name="downvotes", type="integer")
    */
    private $downvotes = 0;
    
    /**
    * @ORM\Column(name="score", type="integer")
    */
    private $score = 0;
  
    /** 
    * @ORM\Column(name="reports", type="integer")
    */
    private $reports = 0;
    
    /**
     * @ORM\Column(name="ip_address", type="string", length=255, nullable=true)
     */
    private $ip_address;
    
    /**
     * @ManyToOne(targetEntity="User", inversedBy="posts")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Post", inversedBy="comments")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post;
  
    /**
    * @var \Datetime $created
    *
    * @Gedmo\Timestampable(on="create")
    * @ORM\Column(type="datetime")
    */
    private $created;
}
