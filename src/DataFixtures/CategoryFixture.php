<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\DataFixtures\BaseFixture;
use App\Entity\Category;

class CategoryFixture extends BaseFixture
{
    protected function loadData()
    {
        $categories = [
            ['Доски и лыжи', 'boards'],
            ['Крепления', 'attachment'],
            ['Ботинки', 'boots'],
            ['Одежда', 'clothing'],
            ['Инструменты', 'tools'],
            ['Разное', 'other']
        ];
        
        foreach ($categories as $key => $cat) {
            $category = new Category();
            $category->setName($cat[0]);
            $category->setSlug($cat[1]);
            
            $this->addReference('category_' . $key, $category);
            
            $this->manager->persist($category);
        }
    }
}
