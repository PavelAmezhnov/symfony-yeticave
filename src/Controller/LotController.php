<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Controller\BaseController;
use App\Entity\Lot;
use App\Entity\Category;
use App\Entity\Bet;
use App\Form\NewLotFormType;
use App\Service\UploadHelper;
use App\Service\BetHelper;

class LotController extends BaseController
{
    /**
     * @Route("/{page}", name="app_homepage", requirements={"page"="\d+"})
     */
    public function index(PaginatorInterface $paginator, int $page = 1)
    {   
        $this->renderParameters['pagination'] = $paginator->paginate(
            $this->em->getRepository(Lot::class)->getOpenLotsQuery(),
            $page,
            9
        );
        
        return $this->render('lot/index.html.twig', $this->renderParameters);
    }
    
    /**
     * @Route("/category/{slug}/{page}", name="app_by_category", requirements={"page"="\d+"})
     */
    public function byCategory(PaginatorInterface $paginator, string $slug, int $page = 1)
    {   
        $category = $this->em->getRepository(Category::class)->findOneBy(['slug' => $slug]);
        
        $this->renderParameters['pagination'] = $paginator->paginate(
            $this->em->getRepository(Lot::class)->getOpenLotsQueryByCategory($category),
            $page,
            9
        );
        
        return $this->render('lot/index.html.twig', $this->renderParameters);
    }
    
    /**
     * @Route("/lot/{id}", name="app_by_id", requirements={"id"="\d+"})
     */
    public function byId(string $id, BetHelper $betHelper)
    {
        $lot = $this->em->getRepository(Lot::class)->getLotById($id, $this->getUser());
        
        if (is_null($lot)) {
            return $this->render('lot/forbidden.html.twig', $this->renderParameters);
        }
        
        $bets = $betHelper->sortBetsByValue($this->em->getRepository(Bet::class)->findBy(['lot' => $lot]));
        
        $this->renderParameters['lot'] = $lot;
        $this->renderParameters['currentPrice'] = $lot->getCurrentPrice();
        $this->renderParameters['countBets'] = count($bets);
        $this->renderParameters['lastBets'] = array_slice($bets, 0, 10);
        
        return $this->render('lot/detail.html.twig', $this->renderParameters);
    }
    
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/lot/new", name="app_new_lot")
     */
    public function new(Request $request, UploadHelper $uploadHelper)
    {
        $form = $this->createForm(NewLotFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $lot = $form->getData();
            $imageFilename = $uploadHelper->saveUpload($request->files->get('new_lot_form')['image'], $uploadHelper::LOT);
            $lot->setImage($imageFilename)->setAuthor($this->getUser());
            
            $this->em->persist($lot);
            $this->em->flush();
            
            return $this->redirectToRoute('app_by_id', ['id' => $lot->getId()]);
        }
        
        $this->renderParameters['newLotForm'] = $form->createView();
        
        return $this->render('lot/new.html.twig', $this->renderParameters);
    }
}
