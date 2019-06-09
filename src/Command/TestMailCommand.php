<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;
use Psr\Container\ContainerInterface;


class TestMailCommand extends Command
{
    protected static $defaultName = 'TestMail';
    protected $mailer;
    protected $twig;
    protected $router;

    public function __construct(ContainerInterface $container, RouterInterface $router)
    {
        parent::__construct();
        
        $this->mailer = $container->get('swiftmailer.mailer.default');
        $this->twig = $container->get('twig');
        $this->router = $router;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->router->getContext()
            ->setHost('syeticave.loc')
            ->setScheme('http');
        
        $url = $this->router->generate('app_by_id', ['id' => '1'], 0);

        $message = (new \Swift_Message('TEST EMAIL'))
            ->setFrom('admin@yeticave.loc')
            ->setTo('pavel.amezhnov@gmail.com')
//            ->setContentType('text/html')
            ->setBody('Blabla');
        
        $this->mailer->send($message);

//        $io->success($url);
    }
}
