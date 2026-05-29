<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */

class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }
    public function findByUserId(int $userId): ?Club
    {
        return $this->createQueryBuilder('c')
        ->join('c.user','u')
        ->where('u.id = :userId')
        ->setParameter('userId',$userId)
        ->getQuery()
        ->getOneOrNullResult();
    }
    public function save(Club $club,bool $flush=true):void
    {
        $this->getEntityManager()->persist($club);
        if($flush){
            $this->getEntityManager()->flush();
        }
    }
    public function findClubsByRolePaginated(string $role, int $page, int $nbre): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->setFirstResult(($page - 1) * $nbre)
            ->setMaxResults($nbre)
            ->getQuery()
            ->getResult();
    }
    public function countClubsByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('count(DISTINCT c.id)')
            ->innerJoin('c.user', 'u')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult();
    }
//    /**
//     * @return Club[] Returns an array of Club objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Club
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
