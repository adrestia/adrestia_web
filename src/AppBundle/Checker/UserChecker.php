<?php

namespace AppBundle\Checker;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;

class EmailNotConfirmedException extends AuthenticationException {
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Email address has not been confirmed yet.';
    }
}

class SuspendedException extends AuthenticationException {
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'This account has been suspended and locked from logging in.';
    }
}

class UserChecker extends BaseUserChecker
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        if(!$user->getEmailConfirmed()) {
            throw new EmailNotConfirmedException();
        }
        
        if($user->getSuspended()) {
            throw new SuspendedException();
        }
        
        parent::checkPreAuth($user);
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        parent::checkPostAuth($user);
        
        // your stuff
    }
}
