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
    private $entityManager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Task::class);
        $this->entityManager = $entityManager;
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
            ->getOneOrNullResult()
        ;
    }

    /**
      *@param array $data
      * @param bool $flush

      * @return Task Returns a Task object
    */
    function saveTask($task)
    {
       try {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
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
        $this->_em->remove($task);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
