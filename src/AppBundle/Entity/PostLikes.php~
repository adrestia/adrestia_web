<?php    
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Entity\User;
use AppBundle\Entity\Post;

/**
* AppBundle\Entity\PostLikes
* @ORM\Entity
* @ORM\Table(name="post_likes")
*
*/  
class PostLikes
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="likes")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $post;
    
    /**
     * @ORM\Column(name="is_like", type="boolean")
     * 
     * Two values here:
     * 1 – true – like
     * 0 – false – dislike
     */
    private $is_like = 0;
    
    /**
    * @var \Datetime $voted
    *
    * @Gedmo\Timestampable(on="create")
    * @ORM\Column(type="datetime")
    */
    private $voted;

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
     * Set isLike
     *
     * @param boolean $isLike
     *
     * @return PostLikes
     */
    public function setIsLike($isLike)
    {
        $this->is_like = $isLike;

        return $this;
    }

    /**
     * Get isLike
     *
     * @return boolean
     */
    public function getIsLike()
    {
        return $this->is_like;
    }

    /**
     * Set voted
     *
     * @param \DateTime $voted
     *
     * @return PostLikes
     */
    public function setVoted($voted)
    {
        $this->voted = $voted;

        return $this;
    }

    /**
     * Get voted
     *
     * @return \DateTime
     */
    public function getVoted()
    {
        return $this->voted;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return PostLikes
     */
    public function setUser(\AppBundle\Entity\User $user)
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
     * Set post
     *
     * @param \AppBundle\Entity\Post $post
     *
     * @return PostLikes
     */
    public function setPost(\AppBundle\Entity\Post $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \AppBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }
}
