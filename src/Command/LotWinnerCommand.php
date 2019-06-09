<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Lot;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;


class LotWinnerCommand extends Command
{
    protected static $defaultName = 'lot:winner';
    protected $em;
    protected $logger;
    protected $mailer;
    protected $twig;
    
    public function __construct(LoggerInterface $logger, ContainerInterface $container, RouterInterface $router)
    {
        parent::__construct();
        
        $this->em       = $container->get('doctrine.orm.default_entity_manager');
        $this->logger   = $logger;
        $this->mailer   = $container->get('swiftmailer.mailer.default');
        $this->twig     = $container->get('twig');
        $this->router   = $router;
        $this->router->getContext()->setHost('syeticave.loc')->setScheme('http');
    }

    protected function configure()
    {
        $this->setDescription('Проставляет победителей у закрытых лотов и создает письма для этих победителей');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lots = $this->em->getRepository(Lot::class)->getFinishedLots();
        
        $countUpdLots = 0;
        foreach ($lots as $lot) {
            $maxBet = -1;
            $winner = null;
            foreach ($lot->getBets() as $bet) {
                if ($maxBet < $value = $bet->getValue()) {
                    $maxBet = $value;
                    $winner = $bet->getAuthor();
                }
            }
            
            if ($winner) {
                $lot->setWinner($winner);
                $this->em->persist($lot);
                
                $message = (new \Swift_Message('Поздравляем!'))
                    ->setFrom('admin@yeticave.loc')
                    ->setTo($winner->getEmail())
                    ->setContentType('text/html')
                    ->setBody($this->twig->render('email/grats.html.twig', [
                        'winner' => $winner,
                        'bet' => $maxBet,
                        'lot' => $lot,
                        'url' => $this->router->generate('app_by_id', ['id' => $lot->getId()], 0)
                    ]));

                $this->mailer->send($message);
                
                $countUpdLots++;
            }
        }

        $this->em->flush();
        
        $mess = sprintf('Обновлено %d лотов', $countUpdLots);
        $this->logger->info($mess);
        $output->writeln($mess);
    }
}
