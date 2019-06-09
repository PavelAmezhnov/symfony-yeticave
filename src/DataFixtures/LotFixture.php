<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\DataFixtures\BaseFixture;
use App\DataFixtures\UserFixture;
use App\DataFixtures\CategoryFixture;
use App\Entity\Lot;

class LotFixture extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData()
    {   
        $referenceNames = array_keys($this->referenceRepository->getReferences());
        $authorReferenceNames = preg_grep('/^user_\d+$/', $referenceNames);
        $winnerReferenceNames = array_merge($authorReferenceNames, [null]);
        $categoryReferenceNames = preg_grep('/^category_\d+$/', $referenceNames);
        
        for ($i = 0; $i < 100; $i++) {
            $lot = new Lot();
            
            $createdAt = $this->faker->dateTimeBetween('-60 days', 'now');
            
            $lot->setName($this->faker->sentence(3, true))
                ->setDescription($this->faker->text(200))
                ->setImage($this->faker->file(__DIR__ . '/img/lot', __DIR__ . '/../../public/uploads/lot', false))
                ->setStartPrice($this->faker->numberBetween(100, 100000))
                ->setEndDate($this->faker->dateTimeBetween('now +1 days', '+31 days'))
                ->setStep($this->faker->numberBetween(10, 10000))
                ->setAuthor($this->getReference($this->faker->randomElement($authorReferenceNames)))
                ->setCreatedAt($createdAt)
                ->setUpdatedAt($this->faker->randomElement([
                    $createdAt,
                    $this->faker->dateTimeInInterval($createdAt, 'now')
                ]));

            $winner = $this->faker->randomElement($winnerReferenceNames);
            if (!is_null($winner)) {
                $lot->setWinner($this->getReference($winner));
            }

            $lot->setCategory($this->getReference($this->faker->randomElement($categoryReferenceNames)));

            $this->addReference('lot_' . $i, $lot);
            
            $this->manager->persist($lot);
        }
    }
    
    public function getDependencies()
    {
        return [
            UserFixture::class,
            CategoryFixture::class
        ];
    }
}
