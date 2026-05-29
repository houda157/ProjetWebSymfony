<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }
    public function save(Event $event, bool $flush = true): void
    {
        $this->getEntityManager()->persist($event);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByClub(int $clubId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.club = :clubId')
            ->setParameter('clubId', $clubId)
            ->orderBy('e.eventDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function searchByTitle(string $q, int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.title LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getAllPosts():array
    {
        return $this->createQueryBuilder('e')
        ->join('e.club','c')
        ->join('c.user','u')
        ->orderBy('e.eventDate','DESC')
        ->getQuery()
        ->getResult();
    }
}
//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
