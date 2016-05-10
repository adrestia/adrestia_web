<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\EmailAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
                $apikey = self::guidv4();
                $entity = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('api_key' => $apikey));
            } while($entity !== null);
            
            // Set their API key
            $user->setApiKey($apikey);
            
            // Create an email confirmation token
            $email_auth = new EmailAuth();
            
            // Generate a new token for confirmation
            do {
                $token = self::guidv4();
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
            self::sendEmail($user->getEmail(), $token);
            
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
    
    protected function guidv4()
    {
        $data = openssl_random_pseudo_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
    }
    
    protected function sendEmail($address, $token) {
        $message = \Swift_Message::newInstance()
                ->setSubject('Welcome to College Confessions!')
                ->setFrom(array('adrestiaweb@gmail.com' => 'College Confessions'))
                ->setTo($address)
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'Emails/registration.html.twig',
                        array('token' => $token)
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        // This is the txt version (non-HTML)
                        'Emails/registration.txt.twig',
                        array('token' => $token)
                    ),
                    'text/plain'
                );
        $this->get('mailer')->send($message);
    }
}
