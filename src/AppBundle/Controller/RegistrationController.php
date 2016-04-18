<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Ramsey\Uuid\Uuid;

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
            
            do {
                $apikey = Uuid::uuid4();
                $entity = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('api_key' => $apikey));
            } while($entity !== null);
            
            $user->setApiKey($apikey);
            
            // Save the user
            $em->persist($user);
            $em->flush();
            
            // Log the user in 
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main',serialize($token));
            
            //TODO: Send verification email here
            
            return $this->redirectToRoute('homepage');
        }
        
        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView())
        );
    }
}
