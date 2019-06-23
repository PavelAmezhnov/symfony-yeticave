<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Service\BetHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class BetController extends BaseController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/bet/new", name="app_new_bet")
     */
    public function new(Request $request, BetHelper $betHelper)
    {   
        $result = $betHelper->addNewBet($request->get('id'), $this->getUser(), $request->get('value'));
        
        if (is_string($result)) {
            return $this->json($result, 500);
        }

        $bets = $betHelper->sortBetsByValue($result);
        
        $this->renderParameters['lot'] = $betHelper->getLot();
        $this->renderParameters['currentPrice'] = $betHelper->getLot()->getCurrentPrice();
        $this->renderParameters['countBets'] = count($bets);
        $this->renderParameters['lastBets'] = array_slice($bets, 0, 10);
        
        return $this->render('lot/_detail_right.html.twig', $this->renderParameters);
    }
}
