<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @return User[] Returns an array of User objects
     */
    public function findAllUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u._user_id', 'ASC')
            ->leftJoin('u.projects', 'p')
            ->leftJoin('u.tasks', 't')
            ->addSelect('p', 't')
            ->orderBy('u._user_id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $_user_id
     * @return User|null Returns a User object or null if not found
     */
    public function findOneByIdUser(string $_user_id)
    {
        return $this->createQueryBuilder('u')
            ->where('u._user_id = :_user_id')
            ->setParameter('_user_id', $_user_id)
            ->leftJoin('u.projects', 'p')
            ->leftJoin('u.tasks', 't')
            ->addSelect('p', 't')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     * @return User|null Returns a User object or null if not found
     */
    public function findOneByEmailUser(string $email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email') // email
            ->setParameter('email', $email)
            ->leftJoin('u.projects', 'p')
            ->leftJoin('u.tasks', 't')
            ->addSelect('p', 't')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return User Returns a User object
     * @param array $data
     * @param bool $flush
     * @return User
     */
    public function saveUser($user)
    {
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return false;
        }
    }

    /**
     * Deletes a User entity from the database.
     *
     * @param User $user
     * @param bool $flush Whether to flush changes immediately.
     * @return void
     */
    public function deleteUser(User $user, bool $flush = true): void
    {
        $this->_em->remove($user);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
