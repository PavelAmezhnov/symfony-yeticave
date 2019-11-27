<?php

namespace App\Service;

use App\Entity\Bet;
use App\Entity\Lot;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class BetHelper
{   
    /**
     * @var EntityManagerInterface
     */
    private $em;
    
    /**
     * @var Lot
     */
    private $lot;
    
    /**
     * @var User
     */
    private $author;
    
    const WRONG_LOT = 'Лот не найден среди открытых';
    const WRONG_AUTHOR = 'Автор лота не может делать на него ставки';
    const WRONG_BET = 'Ставка не может быть ниже минимальной';
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function addNewBet(int $lotId, User $betAuthor, int $betValue)
    {
        if (!$this->isLotOpen($lotId)) {
            return self::WRONG_LOT;
        }
        
        if (!$this->isBetAuthorCorrect($betAuthor)) {
            return self::WRONG_AUTHOR;
        }

        $bets = $this->getLot()->getBets()->toArray();
        if ($betValue < $minBet = $this->lot->getCurrentPrice() + $this->lot->getStep()) {
            return self::WRONG_BET . sprintf(' (%d)', $minBet);
        }
        
        try {
            $newBet = $this->saveBet($betValue);
        } catch (Exception $ex) {
            throw $ex;
        }
        
        $bets[] = $newBet;
        
        return $bets;
    }
    
    private function saveBet(int $betValue)
    {
        $bet = new Bet();
        $bet->setAuthor($this->author)
            ->setLot($this->lot)
            ->setValue($betValue);
        
        try {
            $this->em->persist($bet);
            $this->em->flush();
        } catch (Exception $ex) {
            throw $ex;
        }
        
        return $bet;
    }
    
    public function sortBetsByValue(array $bets): array
    {
        usort($bets, function($bet1, $bet2) { return $bet2->getValue() - $bet1->getValue(); });
        
        return $bets;
    }
    
    public function isLotOpen(int $id): bool
    {
        $this->lot = $this->em->getRepository(Lot::class)->getOpenLotById($id);
        
        return is_null($this->lot) ? false : true;
    }
    
    public function isBetAuthorCorrect(User $author): bool
    {   
        $this->author = $this->em->getRepository(User::class)->find($author->getId());
        
        return is_null($this->author) || $this->lot->getAuthor() === $this->author ? false : true;
    }
    
    public function getLot(): Lot
    {
        return $this->lot;
    }
}
