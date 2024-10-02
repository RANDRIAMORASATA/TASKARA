<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Void_;

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
            ->orderBy('u._id_user', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $_id_user
     * @return User|null Returns a User object or null if not found
     */
    public function findOneByIdUser(string $_id_user)
    {
        return $this->createQueryBuilder('u')
            ->where('u._id_user = :_id_user') // Use the correct field name
            ->setParameter('_id_user', $_id_user)
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
            ->getQuery()
            ->getOneOrNullResult();
    }




    /**
     * @return User Returns a User object
     * @param array $data
     * @param bool $flush
     * @return User
     * 
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
     * @param int $_id_user The ID of the User to delete.
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
