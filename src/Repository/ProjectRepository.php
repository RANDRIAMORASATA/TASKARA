<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */ class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return Project[] Returns an array of Project objects
     */
    public function findAllProjects(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p._id_project', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $_id_project
     * @return Project|null
     */
    public function findOneByIdProject(string $_id_project): ?Project
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p._id_project = :_id_project')
            ->setParameter('_id_project', $_id_project)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }



    /**
     * @param array $data
     * @param bool $flush
     * @return Project Returns a Project object
     */

    public function saveProject(Project $project): Project
    {
        try {
            $this->getEntityManager()->persist($project);
            $this->getEntityManager()->flush();
            return $project;
        } catch (\Exception $e) {
            throw new \RuntimeException('Error saving project: ' . $e->getMessage());
        }
    }




    /**
     * @param string $_id_project
     * @param array $data
     * @param bool $flush
     * @return Project Returns a Project object
     */

    public function deleteProject(Project $project, bool $flush = true): void
    {
        $this->getEntityManager()->remove($project);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findAllProjectByUser(string $_user_id): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')  // 
            ->where('u._user_id = :_user_id')  //
            ->setParameter('_user_id', $_user_id)
            ->getQuery()
            ->getResult();
    }
}
