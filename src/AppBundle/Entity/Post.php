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
* @ORM\Table(name="posts", options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
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
     * @ORM\Column(name="num_reports", type="integer")
     */
    private $num_reports = 0;
    
    /**
     * @ORM\Column(name="hidden", type="boolean")
     */
    private $hidden = false;
    
    /**
     * @ORM\Column(name="ip_address", type="string", length=255, nullable=true)
     */
    private $ip_address;
    
    /**
     * @ManyToOne(targetEntity="User", inversedBy="posts")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
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
     * @OneToMany(targetEntity="Report", mappedBy="post")
     */
    private $reports;
    
    /**
     * @ManyToOne(targetEntity="College")
     */
    private $college;
  
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
        $this->reports = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set numReports
     *
     * @param integer $numReports
     *
     * @return Post
     */
    public function setNumReports($numReports)
    {
        $this->num_reports = $numReports;

        return $this;
    }

    /**
     * Get numReports
     *
     * @return integer
     */
    public function getNumReports()
    {
        return $this->num_reports;
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

    /**
     * Set college
     *
     * @param \AppBundle\Entity\College $college
     *
     * @return Post
     */
    public function setCollege(\AppBundle\Entity\College $college = null)
    {
        $this->college = $college;

        return $this;
    }

    /**
     * Get college
     *
     * @return \AppBundle\Entity\College
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return Post
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Add report
     *
     * @param \AppBundle\Entity\Report $report
     *
     * @return Post
     */
    public function addReport(\AppBundle\Entity\Report $report)
    {
        $this->reports[] = $report;

        return $this;
    }

    /**
     * Remove report
     *
     * @param \AppBundle\Entity\Report $report
     */
    public function removeReport(\AppBundle\Entity\Report $report)
    {
        $this->reports->removeElement($report);
    }
}
