<?php

namespace App\Repository;

use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Find groups by name
     *
     * @param string $name
     * @return Group[] Returns an array of Group objects
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.name LIKE :name')
            ->setParameter('name', "%$name%")
            ->orderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
