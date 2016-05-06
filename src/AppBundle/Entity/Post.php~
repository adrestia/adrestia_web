<?php
  
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
* AppBundle\Entity\Post
* 
* @ORM\Entity
* @ORM\Table(name="posts")
*/  
class Post
{
    /**
    * @ORM\Column(name="id", type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;
    
    /**
    * @ORM\Column(name="body", type="string", length=1023)
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
    * @ORM\Column(type="decimal", precision=20, scale=10)
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
     * @OneToMany(targetEntity="Comment", mappedBy="post")
     */
    private $comments;
    
    /**
     * @OneToMany(targetEntity="PostLikes", mappedBy="post")
     */
    private $likes;
  
    /**
    * @var \Datetime $created
    *
    * @Gedmo\Timestampable(on="create")
    * @ORM\Column(type="datetime")
    */
    private $created;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->likes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set body
     *
     * @param string $body
     *
     * @return Post
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set upvotes
     *
     * @param integer $upvotes
     *
     * @return Post
     */
    public function setUpvotes($upvotes)
    {
        $this->upvotes = $upvotes;

        return $this;
    }

    /**
     * Get upvotes
     *
     * @return integer
     */
    public function getUpvotes()
    {
        return $this->upvotes;
    }

    /**
     * Set downvotes
     *
     * @param integer $downvotes
     *
     * @return Post
     */
    public function setDownvotes($downvotes)
    {
        $this->downvotes = $downvotes;

        return $this;
    }

    /**
     * Get downvotes
     *
     * @return integer
     */
    public function getDownvotes()
    {
        return $this->downvotes;
    }

    /**
     * Set score
     *
     * @param integer $score
     *
     * @return Post
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return integer
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set reports
     *
     * @param integer $reports
     *
     * @return Post
     */
    public function setReports($reports)
    {
        $this->reports = $reports;

        return $this;
    }

    /**
     * Get reports
     *
     * @return integer
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     *
     * @return Post
     */
    public function setIpAddress($ipAddress)
    {
        $this->ip_address = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Post
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Post
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Post
     */
    public function addComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comment $comment
     */
    public function removeComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add like
     *
     * @param \AppBundle\Entity\PostLikes $like
     *
     * @return Post
     */
    public function addLike(\AppBundle\Entity\PostLikes $like)
    {
        $this->likes[] = $like;

        return $this;
    }

    /**
     * Remove like
     *
     * @param \AppBundle\Entity\PostLikes $like
     */
    public function removeLike(\AppBundle\Entity\PostLikes $like)
    {
        $this->likes->removeElement($like);
    }

    /**
     * Get likes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLikes()
    {
        return $this->likes;
    }
}
