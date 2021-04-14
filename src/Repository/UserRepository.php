<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAllWithMoreThan5Posts()
    {
        return $this->getFindAllWithMoreThan5Posts()
            ->getQuery()
            ->getResult();
    }

    public function findAllWithMoreThan5PostsExeptUser(User $user)
    {
        return $this->getFindAllWithMoreThan5Posts()
            ->andHaving('u != :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    private function getFindAllWithMoreThan5Posts(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->select('u')
            ->innerJoin('u.posts', 'mp')
            ->groupBy('u')
            ->having('count(mp) >5');
    }

    public function getAllUsersToFollow(Collection $users)
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->select('u')
            ->where('u.id NOT IN (:following) AND u.enabled = true')
            ->setParameter('following', $users)
            ->getQuery()
            ->getResult();
    }

    public function getAllFollowersForeachUser()
    {
        $qb = $this->createQueryBuilder('u');

            return $qb->select('u as user', 'count(uf.id) as followersCount')
                ->innerJoin('u.followers', 'uf')
                ->groupBy('u')
                ->orderBy('followersCount', 'DESC')
                ->getQuery()
                ->getResult();

    }

    public function getAllFollowingForeachUser(string $query)
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->select('u.username', 'u.id', 'u.fullName')
            ->innerJoin('u.following', 'uf')
            ->where(
                $qb->expr()->like('u.username', ':query')
            )
            ->setParameter('query', '%' . $query . '%')
            ->groupBy('u')
            ->getQuery()
            ->getResult();

    }

    public function getAllFollowing()
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->select('u as user', 'count(uf.id) as followingCount')
            ->innerJoin('u.following', 'uf')
            ->groupBy('u')
            ->orderBy('followingCount', 'DESC')
            ->getQuery()
            ->getResult();

    }
    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
