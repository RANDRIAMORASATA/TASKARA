<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityNotFoundException;



class ProjectController extends AbstractController
{
    #[Route('/project', name: 'get_projects', methods: ['GET'])]
    public function getProjects(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAllProjects();
        error_log("Project : " . $projects);

        $formattedProjects = array_map(function ($project) {
            return [
                '_id_project' => $project->getIdProject(),
                'name_project' => $project->getNameProject(),
                'description_project' => $project->getDescriptionProject(),
                'createdAt' => $project->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $project->getUpdatedAt() ? $project->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'status' => $project->getStatus(),
                'deadline' => $project->getDeadline()->format('Y-m-d H:i:s'),
                '_user_id' => $project->getUser()->getUserId(),

            ];
        }, $projects);
        error_log("Project : " . $this->json(['projects' => $formattedProjects]));

        return $this->json(['projects' => $formattedProjects]);
    }
    #[Route('/project/{_user_id}', name: 'get_projects_user', methods: ['GET'])]
    public function getProjectsUser(string $_user_id, ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAllProjectByUser($_user_id);

        if (empty($projects)) {
            return $this->json(['message' => 'No projects found for this user.'], Response::HTTP_NOT_FOUND);
        }

        $formattedProjects = array_map(function ($project) {
            return [
                '_id_project' => $project->getIdProject(),
                'name_project' => $project->getNameProject(),
                'description_project' => $project->getDescriptionProject(),
                'createdAt' => $project->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $project->getUpdatedAt() ? $project->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'status' => $project->getStatus(),
                'deadline' => $project->getDeadline()->format('Y-m-d H:i:s'),
                '_user_id' => $project->getUser()->getUserId()
            ];
        }, $projects);

        return $this->json(['projects' => $formattedProjects]);
    }

    #[Route('/project/{id_project}', name: 'update_project', methods: ['PUT'])]
    public function getOneProject(
        string $id_project,
        Request $request,
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        $project = $projectRepository->findOneByIdProject($id_project);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        $changesDetected = false;

        $name_project = $data['name_project'] ?? null;
        if ($name_project && $project->getNameProject() !== $name_project) {
            $project->setNameProject($name_project);
            $changesDetected = true;
        }

        $description_project = $data['description_project'] ?? null;
        if ($description_project && $project->getDescriptionProject() !== $description_project) {
            $project->setDescriptionProject($description_project);
            $changesDetected = true;
        }

        $status = $data['status'] ?? null;
        if ($status && $project->getStatus() !== $status) {
            $project->setStatus($status);
            $changesDetected = true;
        }

        $deadline = $data['deadline'] ?? null;
        if ($deadline && $project->getDeadline() !== new \DateTimeImmutable($deadline)) {
            $project->setDeadline(new \DateTimeImmutable($deadline));
            $changesDetected = true;
        }

        if (!$changesDetected) {
            return $this->json(['message' => 'No changes detected'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($project);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while updating the project: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Project updated successfully',
            'project' => [
                '_id_project' => $project->getIdProject(),
                'name_project' => $project->getNameProject(),
                'description_project' => $project->getDescriptionProject(),
                'createdAt' => $project->getCreatedAt() ? $project->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $project->getUpdatedAt() ? $project->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'deadline' => $project->getDeadline() ? $project->getDeadline()->format('Y-m-d H:i:s') : null,
                'status' => $project->getStatus(),
                '_user_id' => $project->getUser()->getUserId(),
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/project', name: 'create_project', methods: ['POST'])]
    public function createProject(
        Request $request,
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        ValidatorInterface $validator
    ): Response {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        $project = new Project();
        $id_project = $data['_id_project'] ?? uniqid();
        $project->setIdProject($id_project);

        // Validate required fields
        if (empty($data['name_project'])) {
            return $this->json(['message' => 'Project name is required'], Response::HTTP_BAD_REQUEST);
        }
        $project->setNameProject($data['name_project']);

        if (empty($data['description_project'])) {
            return $this->json(['message' => 'Project description is required'], Response::HTTP_BAD_REQUEST);
        }
        $project->setDescriptionProject($data['description_project']);

        if (empty($data['status'])) {
            return $this->json(['message' => 'Project status is required'], Response::HTTP_BAD_REQUEST);
        }
        $project->setStatus($data['status']);

        $project->setCreatedAt(new \DateTimeImmutable());
        $project->setDeadline(new \DateTimeImmutable($data['deadline'] ?? 'now'));

        // Handle tasks
        foreach ($data['tasks'] ?? [] as $taskId) {
            $task = $taskRepository->find($taskId);
            if ($task) {
                $project->addTask($task);
            }
        }

        // Validate user ID
        if (empty($data['_user_id'])) {
            return $this->json(['message' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $userRepository->find($data['_user_id']);
            if (!$user) {
                throw new EntityNotFoundException('User not found');
            }
            $project->setUser($user);
        } catch (EntityNotFoundException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        // Validate project
        $errors = $validator->validate($project);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        try {
            $savedProject = $projectRepository->saveProject($project);

            return $this->json([
                'message' => 'Project created successfully',
                'project' => [
                    '_id_project' => $savedProject->getIdProject(),
                    'name_project' => $savedProject->getNameProject(),
                    'description_project' => $savedProject->getDescriptionProject(),
                    'createdAt' => $savedProject->getCreatedAt()->format('Y-m-d H:i:s'),
                    'status' => $savedProject->getStatus(),
                    'deadline' => $savedProject->getDeadline()->format('Y-m-d H:i:s'),
                    '_user_id' => $savedProject->getUser()->getUserId(),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error saving project: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/project/{id_project}', name: 'delete_project', methods: ['DELETE'])]
    public function deleteProject(
        string $id_project,
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $project = $projectRepository->findOneByIdProject($id_project);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }
        try {
            $entityManager->remove($project);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 500);
        }

        return $this->json(['message' => 'Project deleted successfully']);
    }
}
