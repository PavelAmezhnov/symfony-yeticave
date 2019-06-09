<?php

namespace App\Repository;

use App\Entity\Lot;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Lot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lot[]    findAll()
 * @method Lot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LotRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Lot::class);
    }
    
    public function getOpenLotsQuery()
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.endDate > :currentTime')
            ->setParameter('currentTime', new \DateTime())
            ->leftJoin('l.bets', 'b')
            ->addSelect('b')
            ->orderBy('l.updatedAt', 'DESC')
            ->getQuery();
    }
    
    public function getOpenLotsQueryByCategory(Category $category)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.endDate > :currentTime')
            ->setParameter('currentTime', new \DateTime())
            ->andWhere('l.category = :category')
            ->setParameter('category', $category)
            ->leftJoin('l.bets', 'b')
            ->addSelect('b')
            ->orderBy('l.updatedAt', 'DESC')
            ->getQuery();
    }

    public function getOpenLotsQueryByQueryString(string $queryString)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.endDate > :currentTime')
            ->setParameter('currentTime', new \DateTime())
            ->andWhere('l.name LIKE :queryString OR l.description LIKE :queryString')
            ->setParameter('queryString', '%' . $queryString . '%')
            ->leftJoin('l.bets', 'b')
            ->addSelect('b')
            ->orderBy('l.updatedAt', 'DESC')
            ->getQuery();
    }
    
    public function getOpenLotById(int $id)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.id = :id')
            ->setParameter('id', $id)
            ->andWhere('l.endDate > :currentTime')
            ->setParameter('currentTime', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function getLotById(int $id, User $user = null)
    {
        $qb = $this->createQueryBuilder('l')->andWhere('l.id = :id')->setParameter('id', $id);
        
        if ($user) {
            $qb->andWhere('l.endDate > :currentTime OR l.author = :user OR l.winner = :user')
                ->setParameter('currentTime', new \DateTime())
                ->setParameter('user', $user);
        } else {
            $qb->andWhere('l.endDate > :currentTime')->setParameter('currentTime', new \DateTime());
        }
        
        return $qb->getQuery()->getOneOrNullResult();
    }
    
    public function getFinishedLots()
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.winner IS NULL')
            ->andWhere('l.endDate <= :currentTime')
            ->setParameter('currentTime', new \DateTime())
            ->innerJoin('l.bets', 'b')
            ->addSelect('b')
            ->innerJoin('b.author', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }
}
