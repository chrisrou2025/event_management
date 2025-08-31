<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Récupère les événements à venir (date >= aujourd'hui)
     * Triés par date croissante
     */
    public function findUpcomingEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.date >= :today')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les événements de ce mois
     */
    public function findEventsThisMonth(): array
    {
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');

        return $this->createQueryBuilder('e')
            ->where('e.date BETWEEN :start AND :end')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de participants uniques dans tous les événements
     */
    public function countUniqueParticipants(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT p.id)')
            ->leftJoin('e.participants', 'p')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    /**
     * Trouve les événements les plus populaires (avec le plus de participants)
     */
    public function findMostPopularEvents(int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.participants', 'p')
            ->groupBy('e.id')
            ->orderBy('COUNT(p.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
