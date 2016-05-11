<?php
# src/Acme/DemoBundle/Command/CreateClientCommand.php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\College;
use AppBundle\Entity\User;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('adrestia:new-user')
            ->setDescription('Creates a new user with the required parameters')    
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Please put in an email address for the user.'  
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Please put in the password for the user.'
            )
            ->addArgument(
                'college_id',
                InputArgument::REQUIRED,
                'Add in the id of the college. Get from database.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $plain_password = $input->getArgument('password');
        $college_id = $input->getArgument('college_id');
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        $college = $em->getRepository("AppBundle:College")->find($college_id);
        
        $user = new User();
        
        $user->setEmail($email);
        $user->setEmailConfirmed(true);
        $user->setCollege($college);
        
        $password = $this->getContainer()
            ->get('security.password_encoder')
            ->encodePassword($user, $plain_password);
         $user->setPassword($password);
        
         do {
             $apikey = self::guidv4();
             $entity = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('api_key' => $apikey));
         } while($entity !== null);
         
         // Set their API key
         $user->setApiKey($apikey);
         
         // Save the user
         $em->persist($user);
         $em->flush();
        
        $output->writeln("Successfully created a new user.");
    }
    
    protected function guidv4()
    {
        $data = openssl_random_pseudo_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
    }
}