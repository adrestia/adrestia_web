<?php
  
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
* AppBundle\Entity\User
* 
* @ORM\Table(name="users")
* @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
* @UniqueEntity(fields="email", message="Email already taken")
*
*/  
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
  
    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;
  
    /**
     * @ORM\Column(name="email_confirmed", type="boolean") 
     */
    protected $email_confirmed = false;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    protected $plainPassword;
  
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;
  
    /**
     * @ORM\Column(name="score", type="integer")
     */
    protected $score = 0;
  
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
    protected $account_status = 0;
    
    /**
     * @ManyToOne(targetEntity="College")
     */
    private $college;
  
    /**
     * @ORM\Column(name="suspended", type="boolean")
     */
    protected $suspended = false;
  
    /**
     * @var \Datetime $suspended_date
     *
     * @ORM\Column(name="suspended_date", type="datetime", nullable=true)
     *
     * The date for when the user is able to access their account again
     */
    protected $suspended_date;
  
    /** 
     * @ORM\Column(name="reports", type="integer")
     */
    protected $reports = 0;
  
    /**
     * @OneToMany(targetEntity="Post", mappedBy="user")
     */
    protected $posts;
  
    /**
     * @OneToMany(targetEntity="PostLikes", mappedBy="user")
     *
     * This includes dislikes
     */
    protected $post_likes;
    
    /**
     * @OneToMany(targetEntity="CommentLikes", mappedBy="user")
     *
     * This includes dislikes
     */
    protected $comment_likes;
  
    /**
     * @OneToMany(targetEntity="Comment", mappedBy="user")
     */
    protected $comments;
  
    /**
     * @var \Datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;
  
    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;
  
    /**
     * @var \DateTime $account_changed
     *
     * @ORM\Column(name="account_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"email", "password"})
     */
    protected $account_changed;  
  
    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $is_active; 
    
    /**
     * @ORM\Column(name="api_key", type="guid")
     */
    protected $api_key;
    
    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="users_roles",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    protected $roles;

    /**
     * @param mixed $apiKey
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    public function __construct()
    {
        $this->is_active = true;
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->posts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roles[] = "ROLE_USER";
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getSalt()
    {
        // THIS IS BECAUSE WE USE BCRYPT
        // NORMALLY YOU ALWAYS WANT A SALT
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Add role
     *
     * @param \AppBundle\Entity\Role $role
     *
     * @return Role
     */
    public function addRole(\AppBundle\Entity\Role $role)
    {
        $role->addUser($this);
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \AppBundle\Entity\Role $role
     */
    public function removeRole(\AppBundle\Entity\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }
    
    /**
     * Set roles
     *
     * @param \AppBundle\Entity\Role $roles
     *
     * @return User
     */
    public function setRoles(Array $roles = null)
    {
        $this->roles->clear();
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials()
    {
    }
  
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
        $this->id,
        $this->email,
        $this->password,
        ) = unserialize($serialized);
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
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailConfirmed
     *
     * @param boolean $emailConfirmed
     *
     * @return User
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->email_confirmed = $emailConfirmed;

        return $this;
    }

    /**
     * Get emailConfirmed
     *
     * @return boolean
     */
    public function getEmailConfirmed()
    {
        return $this->email_confirmed;
    }
    
    /**
     * Get plainPassword
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set plainPassword
     *
     * @param string $password
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set score
     *
     * @param integer $score
     *
     * @return User
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
     * Set accountStatus
     *
     * @param integer $accountStatus
     *
     * @return User
     */
    public function setAccountStatus($accountStatus)
    {
        $this->account_status = $accountStatus;

        return $this;
    }

    /**
     * Get accountStatus
     *
     * @return integer
     */
    public function getAccountStatus()
    {
        return $this->account_status;
    }

    /**
     * Set suspended
     *
     * @param boolean $suspended
     *
     * @return User
     */
    public function setSuspended($suspended)
    {
        $this->suspended = $suspended;

        return $this;
    }

    /**
     * Get suspended
     *
     * @return boolean
     */
    public function getSuspended()
    {
        return $this->suspended;
    }

    /**
     * Set suspendedDate
     *
     * @param \DateTime $suspendedDate
     *
     * @return User
     */
    public function setSuspendedDate($suspendedDate)
    {
        $this->suspended_date = $suspendedDate;

        return $this;
    }

    /**
     * Get suspendedDate
     *
     * @return \DateTime
     */
    public function getSuspendedDate()
    {
        return $this->suspended_date;
    }

    /**
     * Set reports
     *
     * @param integer $reports
     *
     * @return User
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
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get accountChanged
     *
     * @return \DateTime
     */
    public function getAccountChanged()
    {
        return $this->account_changed;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return User
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return User
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Set accountChanged
     *
     * @param \DateTime $accountChanged
     *
     * @return User
     */
    public function setAccountChanged($accountChanged)
    {
        $this->account_changed = $accountChanged;

        return $this;
    }

    /**
     * Add post
     *
     * @param \AppBundle\Entity\Post $post
     *
     * @return User
     */
    public function addPost(\AppBundle\Entity\Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * Remove post
     *
     * @param \AppBundle\Entity\Post $post
     */
    public function removePost(\AppBundle\Entity\Post $post)
    {
        $this->posts->removeElement($post);
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return User
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
     * Set college
     *
     * @param \AppBundle\Entity\College $college
     *
     * @return User
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
     * Add postLike
     *
     * @param \AppBundle\Entity\PostLikes $postLike
     *
     * @return User
     */
    public function addPostLike(\AppBundle\Entity\PostLikes $postLike)
    {
        $this->post_likes[] = $postLike;

        return $this;
    }

    /**
     * Remove postLike
     *
     * @param \AppBundle\Entity\PostLikes $postLike
     */
    public function removePostLike(\AppBundle\Entity\PostLikes $postLike)
    {
        $this->post_likes->removeElement($postLike);
    }

    /**
     * Get postLikes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPostLikes()
    {
        return $this->post_likes;
    }

    /**
     * Add commentLike
     *
     * @param \AppBundle\Entity\CommentLikes $commentLike
     *
     * @return User
     */
    public function addCommentLike(\AppBundle\Entity\CommentLikes $commentLike)
    {
        $this->comment_likes[] = $commentLike;

        return $this;
    }

    /**
     * Remove commentLike
     *
     * @param \AppBundle\Entity\CommentLikes $commentLike
     */
    public function removeCommentLike(\AppBundle\Entity\CommentLikes $commentLike)
    {
        $this->comment_likes->removeElement($commentLike);
    }

    /**
     * Get commentLikes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentLikes()
    {
        return $this->comment_likes;
    }
}
