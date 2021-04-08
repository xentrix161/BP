<?php

namespace App\Repository;

use App\Entity\ShoppingCart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShoppingCart|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShoppingCart|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShoppingCart[]    findAll()
 * @method ShoppingCart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShoppingCartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShoppingCart::class);
    }

    // /**
    //  * @return ShoppingCart[] Returns an array of ShoppingCart objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShoppingCart
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
