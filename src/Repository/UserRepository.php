<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // CREATE - this is handled by the EntityManager in the controller
    // So no explicit method is needed here

    // READ - Get a single user by ID
    public function findUserById(int $id): ?User
    {
        return $this->find($id);
    }

    // READ - Get a single user by email
    public function findUserByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    // READ - Get all users
    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    // READ - Get users by role
    public function findUsersByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%'.$role.'%')
            ->getQuery()
            ->getResult();
    }

    // UPDATE - This is done using Doctrine's EntityManager in the controller
    // No explicit method needed for update in the repository

    // DELETE - Delete user by ID
    public function deleteUserById(int $id): void
    {
        $user = $this->findUserById($id);

        if ($user) {
            $this->_em->remove($user);
            $this->_em->flush();
        }
    }
}
