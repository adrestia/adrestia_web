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

class DeleteUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('adrestia:delete-user')
            ->setDescription('Deletes a user. Better than removing from database manually.')    
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Add in the id of the user. Get from database if unknown.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the argument
        $email = $input->getArgument('email');
        
        // Get the user
        $em = $this->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository("AppBundle:User")->findOneBy(array('email' => $email));
         
         // Delete the user
         $em->remove($user);
         $em->flush();
        
        $output->writeln("Successfully deleted " . $user->getEmail() . " from database.");
    }
}