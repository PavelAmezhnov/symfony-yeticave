<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

abstract class BaseFixture extends Fixture
{
    protected $manager;
    protected $faker;
    
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        
        $this->loadData();

        $this->manager->flush();
    }
    
    abstract protected function loadData();
}
