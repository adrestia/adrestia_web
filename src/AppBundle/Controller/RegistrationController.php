<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\EmailAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Helper\Utilities;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function indexAction(Request $request)
    {
        // Build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        
        // Handle Request
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            
            // Encode the password
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // Get a unique API key
            do {
                $apikey = Utilities::guidv4();
                $entity = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('api_key' => $apikey));
            } while($entity !== null);
            
            // Set their API key
            $user->setApiKey($apikey);
            
            // Create an email confirmation token
            $email_auth = new EmailAuth();
            
            // Generate a new token for confirmation
            do {
                $token = Utilities::guidv4();
                $entity = $em->getRepository('AppBundle:EmailAuth')->findOneBy(array('token' => $token));
            } while($entity !== null);
            
            // Configure the confirmation token
            $email_auth->setToken($token);
            $email_auth->setUser($user);
            
            // Save the user
            $em->persist($user);
            $em->persist($email_auth);
            $em->flush();
            
            // Send the confirmation email
            Utilities::sendEmail($user->getEmail(), $token, $this);
            
            // Show the confirmation email
            return $this->render(
                'registration/confirm.html.twig', [
                    'email' => $user->getEmail()
                ]
            );
        }
        
        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView())
        );
    }
    
    /**
     * @Route("/confirm/{token}", name="confirm_email")
     */
    public function confirmEmailAction(Request $request, $token) {
        
        $em = Utilities::getEntityManager($this);
        
        $auth = $em->getRepository("AppBundle:EmailAuth")
                   ->findOneBy(array('token' => $token, 'verified' => false));
        
        if(!$auth) {
            return $this->render(
                'security/confirm.html.twig',
                array(
                    'error' => 'Token is invalid or has already been used',
                )
            );
        }
            
        $user = $em->getRepository("AppBundle:User")
                   ->find($auth->getUser());
        
        if(!$user) {
            return $this->render(
                'security/confirm.html.twig',
                array(
                    'error' => 'User could not be found. Please contact support at adrestiaweb@gmail.com.',
                )
            );
        }
        
        $user->setEmailConfirmed(true);
        $auth->setVerified(true);
        $em->persist($user);
        $em->persist($auth);
        $em->flush();
        
        return $this->render(
            'security/confirm.html.twig'
        );
    }
    
    /**
     * @Route("/suffix", name="email_suffix")
     * @Method({"POST"})
     */
    public function suffixAction(Request $request) {
        $name = $request->get('college');
        
        try {
            $em = Utilities::getEntityManager($this);
        
            $college = $em->getRepository('AppBundle:College')
                          ->findOneBy(array('name' => $name));
            
            if(!$college) {
                throw $this->createNotFoundException(
                    'No college found with name ' . $name
                );
            }
            
            $suffix = $college->getSuffix();
            
            return new JsonResponse(array('status' => 200, 'suffix' => $suffix));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to get suffix.'));
        }   
    }
}
