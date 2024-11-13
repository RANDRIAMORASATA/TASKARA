<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[] Returns an array of task objects
     */
    function findAllTasks(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t._id_task', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $_id_task
     * @return Task Returns a Task object
     */
    function findOneByIdTask(string $_id_task): ?Task
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t._id_task = :_id_task')
            ->setParameter('_id_task', $_id_task)
            ->getQuery()
            ->getOneOrNullResult();
    }


    /**
     *@param array $data
     * @param bool $flush

     * @return Task Returns a Task object
     */
    function saveTask(Task $task): Task
    {
        try {
            $this->getEntityManager()->persist($task);
            $this->getEntityManager()->flush();
            return $task;
        } catch (\Exception $e) {
            throw new \RuntimeException('Error saving task: ' . $e->getMessage());
        }
    }


    /**
     * @param string $_id_task
     * @param array $data
     * @param bool $flush
     * @return Task Returns a Task object
     */

    function deleteTask(Task $task, bool $flush = true): void
    {
        $this->getEntityManager()->remove($task);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllTaskByUser(string $user_id): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->where('u._user_id = :user_id')  // Utilisez '_user_id' comme clé de jointure
            ->setParameter('user_id', $user_id)  // Le paramètre est maintenant 'user_id'
            ->getQuery()
            ->getResult();
    }

    public function findUrgentTasksByUser(string $_user_id): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->where('u._user_id = :user_id')
            ->andWhere('t.isUrgent = :isUrgent')
            ->setParameter('user_id', $_user_id)
            ->setParameter('isUrgent', true)  // Filter for urgent tasks
            ->getQuery()
            ->getResult();
    }
}
