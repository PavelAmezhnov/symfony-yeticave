<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\SearchFormType;
use App\Entity\Category;

abstract class BaseController extends AbstractController
{
    protected $em;
    protected $renderParameters = [];
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $this->renderParameters['categories'] = $this->em->getRepository(Category::class)->findAll();
        $this->renderParameters['searchForm'] = $this->get('form.factory')
            ->createNamed(null, SearchFormType::class, null, ['action' => $this->generateUrl('app_search')])
            ->createView();
        
        $parameters = empty($parameters) ? $this->renderParameters : array_merge($parameters, $this->renderParameters);
        
        return parent::render($view, $parameters, $response);
    }
}
