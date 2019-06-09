<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\DataFixtures\BaseFixture;
use App\DataFixtures\UserFixture;
use App\DataFixtures\LotFixture;
use App\Entity\Bet;

class BetFixture extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData()
    {
        $referenceNames = array_keys($this->referenceRepository->getReferences());
        $lotReferenceNames = preg_grep('/^lot_\d+$/', $referenceNames);
        $userReferenceNames = preg_grep('/^user_\d+$/', $referenceNames);
        
        foreach ($lotReferenceNames as $lotRef) {
            $betCount = $this->faker->numberBetween(0, 10);
            $minBet = $this->getReference($lotRef)->getStartPrice() + $this->getReference($lotRef)->getStep();
            
            $lotAuthor = $this->getReference($lotRef)->getAuthor();
            $authorRef = null;
            foreach ($userReferenceNames as $userRef) {
                if ($lotAuthor->getId() === $this->getReference($userRef)->getId()) {
                    $authorRef = $userRef;
                    break;
                }
            }
            $betAuthorRef = array_diff($userReferenceNames, [$authorRef]);
            
            $minTime = $this->getReference($lotRef)->getUpdatedAt();
            
            for ($i = 0; $i <= $betCount; $i++) {
                $bet = new Bet();
                $bet->setLot($this->getReference($lotRef))
                    ->setAuthor($this->getReference($this->faker->randomElement($betAuthorRef)));
                
                $newBet = $this->faker->numberBetween($minBet, 100000);
                $bet->setValue($newBet);
                $minBet = $newBet;
                
                $newBetTime = $this->faker->dateTimeBetween($minTime, 'now');
                $bet->setCreatedAt($newBetTime)->setUpdatedAt($newBetTime);
                $minTime = $newBetTime;
                
                $this->manager->persist($bet);
            }
        }
    }
    
    public function getDependencies()
    {
        return [
            UserFixture::class,
            LotFixture::class
        ];
    }
}
