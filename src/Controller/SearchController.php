<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Controller\BaseController;
use App\Entity\Lot;
use App\Form\SearchFormType;

class SearchController extends BaseController
{
    /**
     * @Route("/search/{page}", name="app_search", methods={"GET"}, requirements={"page"="\d+"})
     */
    public function search(Request $request, PaginatorInterface $paginator, int $page = 1)
    {
        $form = $this->get('form.factory')->createNamed(null, SearchFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->renderParameters['pagination'] = $paginator->paginate(
                $this->em->getRepository(Lot::class)->getOpenLotsQueryByQueryString($form['q']->getData()),
                $page,
                9
            );
        }
        
        return $this->render('search/search.html.twig');
    }
    
    public function advancedSearch()
    {
        
    }
}
