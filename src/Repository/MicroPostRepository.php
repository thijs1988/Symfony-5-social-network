<?php

namespace App\Repository;

use App\Entity\MicroPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MicroPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method MicroPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method MicroPost[]    findAll()
 * @method MicroPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MicroPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicroPost::class);
    }

    public function findAllByUsers(Collection $users)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select('p')
            ->where('p.user IN (:following)')
            ->setParameter('following', $users)
            ->orderBy('p.time', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function getViewsForeachPost(){
        $qb = $this->createQueryBuilder('p');

        return $qb->select('p as post', 'count(pc.id) as viewCount')
            ->innerJoin('p.counter', 'pc')
            ->groupBy('p')
            ->orderBy('viewCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getLikesForeachPost(){
        $qb = $this->createQueryBuilder('p');

        return $qb->select('p as post', 'count(pl.id) as likeCount')
            ->innerJoin('p.likedBy', 'pl')
            ->groupBy('p')
            ->orderBy('likeCount', 'DESC')
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return MicroPost[] Returns an array of MicroPost objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MicroPost
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
