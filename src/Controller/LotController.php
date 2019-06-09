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
        
        return $this->render('lot/index.html.twig');
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
        
        return $this->render('lot/index.html.twig');
    }
    
    /**
     * @Route("/lot/{id}", name="app_by_id", requirements={"id"="\d+"})
     */
    public function byId(string $id)
    {
        $this->renderParameters['lot'] = $lot = $this->em->getRepository(Lot::class)->getLotById($id, $this->getUser());
        $bets = $this->em->getRepository(Bet::class)->findBy(['lot' => $lot], ['updatedAt' => 'DESC']);
        $this->renderParameters['countBets'] = count($bets);
        $this->renderParameters['lastBets'] = array_slice($bets, 0, 10);
        
        return $this->render('lot/detail.html.twig');
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
            $lot->setImage($imageFilename);
            $lot->setAuthor($this->getUser());
            
            $this->em->persist($lot);
            $this->em->flush();
            
            return $this->redirectToRoute('app_by_id', ['id' => $lot->getId()]);
        }
        
        $this->renderParameters['newLotForm'] = $form->createView();
        
        return $this->render('lot/new.html.twig');
    }
}
