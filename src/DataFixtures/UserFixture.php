<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\DataFixtures\BaseFixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixture extends BaseFixture
{
    private $encoder;
    
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
    public function loadData()
    {   
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($this->faker->email)
                ->setName($this->faker->firstName() . ' ' . $this->faker->lastName)
                ->setContacts($this->faker->address)
                ->setPassword($this->encoder->encodePassword($user, $this->faker->password));
            
            $this->addReference('user_' . $i, $user);
            
            $this->manager->persist($user);
        }
    }
}
