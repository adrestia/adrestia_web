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
}