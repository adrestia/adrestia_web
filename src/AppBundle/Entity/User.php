<?php
  
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
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
    * @ORM\Column(name="id", type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;
  
    /**
    * @ORM\Column(name="email", type="string", length=255, unique=true)
    * @Assert\NotBlank()
    * @Assert\Email()
    */
    private $email;
  
    /**
    * @ORM\Column(name="email_confirmed", type="boolean") 
    */
    private $email_confirmed = false;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;
  
    /**
    * @ORM\Column(type="string", length=100)
    */
    private $password;
  
    /**
    * @ORM\Column(name="score", type="integer")
    */
    private $score = 0;
  
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
    * @ORM\Column(name="suspended_date", type="datetime", nullable=true)
    *
    * The date for when the user is able to access their account again
    */
    private $suspended_date;
  
    /** 
    * @ORM\Column(name="reports", type="integer")
    */
    private $reports = 0;
  
    /**
    * #@OneToMany(targetEntity="Post", mappedBy="user")
    */
    //private $posts;
  
    /**
    * #@OneToMany(targetEntity="Like", mappedBy="user")
    *
    * This includes dislikes
    */
    //private $likes;
  
    /**
    * #@OneToMany(targetEntity="Comment", mappedBy="user")
    */
    //private $comments;
  
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
    private $account_changed;  
  
    /**
    * @ORM\Column(name="is_active", type="boolean")
    */
    private $is_active = true; 

    public function __construct()
    {
        $this->isActive = true;
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

    public function getRoles()
    {
        return array('ROLE_USER');
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
}
