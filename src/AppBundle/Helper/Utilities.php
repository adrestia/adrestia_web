<?php

namespace AppBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    
class Utilities extends Controller
{
    
    /**
     * @param $context – pass in $this as the variable
     * @return Doctrine entity manager
     */
    public static function getEntityManager($context) {
        return $context->get('doctrine')->getManager();
    }
    
    /**
     * @param $context – pass in $this as the variable
     * @return IP Address from the request
     */
    public static function getCurrentIp($context) {
        return $context->container->get('request_stack')->getMasterRequest()->getClientIp();
    }
    
    /**
     * @param $context – pass in $this as the variable
     * @return the User object that is currently authenticated
     */
    public static function getCurrentUser($context) {
        return $context->get('security.token_storage')->getToken()->getUser();
    }
    
    /**
     * The reddit hotness algorithm!
     *
     * @param $ups – Number of post upvotes
     * @param $downs – Number of post downvotes
     * @param $date – When the post was submitted
     *
     * @return calculated score of how hot a post is
     */
    public static function hot($ups, $downs, $date) {
        $score = $ups - $downs;
        $order = log10(max(abs($score), 1));
        
        if($score > 0) {
            $sign = 1;
        } elseif($score < 0) {
            $sign = -1;
        } else {
            $sign = 0;
        }
        
        $seconds = $date->getTimestamp() - 1134028003;
        
        return round($order * $sign + $seconds / 45000, 7);
    }
    
    /**
     * @return GUID version 4. Going to be unique.
     */
    public static function guidv4()
    {
        $data = openssl_random_pseudo_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * @param $address – String – Email address
     * @param $token – String – Unique token generated for confirmation
     *
     * @return bool – whether the send was successful or not
     */
    public static function sendEmail($address, $token) {
        $message = \Swift_Message::newInstance()
                ->setSubject('Welcome to College Confessions!')
                ->setFrom(array('adrestiaweb@gmail.com' => 'College Confessions'))
                ->setTo($address)
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'emails/registration.html.twig',
                        array('token' => $token)
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        // This is the txt version (non-HTML)
                        'emails/registration.txt.twig',
                        array('token' => $token)
                    ),
                    'text/plain'
                );
        return $this->get('mailer')->send($message);
    }
}