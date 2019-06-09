<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Controller\BaseController;
use App\Entity\Bet;
use App\Entity\Lot;

class BetController extends BaseController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/bet/new", name="app_new_bet")
     */
    public function new(Request $request)
    {
        $lot = $this->em->getRepository(Lot::class)->getOpenLotById($request->get('id'));
        
        if (is_null($lot)) {
            return $this->json('Лот не найден среди открытых', 500);
        }
        if ($lot->getAuthor() === $this->getUser()) {
            return $this->json('Автор лота не может делать на него ставки', 500);
        }
        $bets = $this->em->getRepository(Bet::class)->findBy(['lot' => $lot], ['updatedAt' => 'DESC']);
        $currentPrice = empty($bets) ? $lot->getStartPrice() : $bets[0]->getValue();
        if ($currentPrice + $lot->getStep() > $request->get('value')) {
            return $this->json(sprintf('Ставка не может быть ниже минимальной (%d)', $currentPrice + $lot->getStep()), 500);
        }
        
        $bet = new Bet();
        $bet->setAuthor($this->getUser())
            ->setLot($lot)
            ->setValue($request->get('value'));
        $this->em->persist($bet);
        $this->em->flush();
        
        $this->renderParameters['lot'] = $lot;
        $this->renderParameters['countBets'] = count($bets) + 1;
        $this->renderParameters['lastBets'] = array_merge([$bet], array_slice($bets, 0, 9));
        
        return $this->render('lot/_detail_right.html.twig');
    }
}
