<?php
    
namespace AppBundle\Form\Model;


use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPassword
{
    /**
     * @Assert\Length(
     *     min = 8,
     *     minMessage = "Password should by at least 8 chars long"
     * )
     */
     protected $newPassword;
     
     public function getNewPassword() {
         return $this->newPassword;
     }
     
     public function setNewPassword($newPassword) {
         $this->newPassword = $newPassword;
     }
}