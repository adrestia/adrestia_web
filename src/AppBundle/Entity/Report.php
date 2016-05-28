<?php
  
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
* AppBundle\Entity\Report
* 
* @ORM\Entity
* @ORM\Table(name="reports")
*
*/  
class Report
{
    /**
    * @ORM\Column(name="id", type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="ReportReason")
     * @JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $reason;
    
    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="reports")
     * @JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $post;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;
    

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
     * Set reason
     *
     * @param \AppBundle\Entity\ReportReason $reason
     *
     * @return Report
     */
    public function setReason(\AppBundle\Entity\ReportReason $reason = null)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return \AppBundle\Entity\ReportReason
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set post
     *
     * @param \AppBundle\Entity\Post $post
     *
     * @return Report
     */
    public function setPost(\AppBundle\Entity\Post $post = null)
    {
        $this->post = $post;
        $post->addReport($this);
        $post->setNumReports($post->getNumReports() + 1);
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

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Report
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
}
