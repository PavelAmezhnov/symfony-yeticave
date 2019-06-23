<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Form\SearchFormType;
use App\Entity\Category;

abstract class BaseController extends AbstractController
{
    protected $em;
    protected $renderParameters = [];
    
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.default_entity_manager');
        
        $this->renderParameters['categories'] = $this->em->getRepository(Category::class)->findAll();
        $this->renderParameters['searchForm'] = $container->get('form.factory')
            ->createNamed(null, SearchFormType::class, null, ['action' => $container->get('router')->generate('app_search', [], 1)])
            ->createView();
    }
}
