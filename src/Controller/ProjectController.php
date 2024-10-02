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

class ProjectController extends AbstractController
{
    #[Route('/project', name: 'get_projects', methods: ['GET'])]
    public function getProjects(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAllProjects();
        return $this->json(['projects' => $projects]);
    }

    #[Route('/project/{_id_project}', name: 'get_project', methods: ['GET'])]
    public function getOneProject(string $_id_project, ProjectRepository $projectRepository): Response
    {
        $project = $projectRepository->findOneByIdProject($_id_project);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(['project' => $project]);
    }


    #[Route('/project', name: 'create_project', methods: ['POST'])]
    public function createProject(
        Request $request,
        EntityManagerInterface $entityManager,
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        ValidatorInterface $validator
    ): Response {
<<<<<<< HEAD
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }
        // Log all request parameters
=======
        $data = json_decode($request->getContent(), true);
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
        error_log("Request data: " . json_encode($data));

        $project = new Project();
        $id_project = $data['_id_project'] ?? uniqid();
        $project->setIdProject($id_project);

        $name_project = $data['name_project'] ?? null;
        error_log("Name project: " . $name_project);
        if (empty($name_project)) {
            return $this->json(['message' => 'Project name is required'], Response::HTTP_BAD_REQUEST);
        }
        $project->setNameProject($name_project);

        $description_project = $data['description_project'] ?? null;
        if (empty($description_project)) {
            return $this->json(['message' => 'Project description is required'], Response::HTTP_BAD_REQUEST);
        }
        $project->setDescriptionProject($description_project);

        $status = $data['status'] ?? null;
        if (empty($status)) {
            return $this->json(['message' => 'Project status is required'], Response::HTTP_BAD_REQUEST);
        }
        $project->setStatus($status);

        $createdAt = new \DateTimeImmutable();
        $project->setCreatedAt($createdAt);

        $deadline = new \DateTimeImmutable();
<<<<<<< HEAD
        if (empty($deadline)) {
            return $this->json(['message' => 'Project deadline is required'], Response::HTTP_BAD_REQUEST);
        }
        $project->setDeadline($deadline);

        $taskIds = $data['tasks'] ?? [];
        if (!is_array($taskIds)) {
            return $this->json(['message' => 'Tasks should be an array'], Response::HTTP_BAD_REQUEST);
        }
=======
        $project->setDeadline($deadline);

        $taskIds = $data['tasks'] ?? [];
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3

        // Process tasks
        foreach ($taskIds as $taskId) {
            $task = $taskRepository->find($taskId);
            if ($task) {
                $project->addTask($task);
            }
        }

        // Validate user ID
        $userId = $data['_user_id'] ?? null;
        error_log("User ID from request: " . $userId);

        if (empty($userId)) {
            return $this->json(['message' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $project->setUser($user);

        $errors = $validator->validate($project);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        try {
            $projectRepository->saveProject($project);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Project created successfully',
            'project' => $project
        ], Response::HTTP_CREATED);
    }





    #[Route('/project/{id_project}', name: 'update_project', methods: ['PUT'])]
    public function updateProject(
        string $id_project,
        Request $request,
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $requestData = $request->request->all();
        error_log("Request data: " . json_encode($requestData));
        $project = $projectRepository->findOneByIdProject($id_project);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }
        $changesDetected = false;
        $name_project = $request->request->get('name_project');
        if ($name_project) {
            $project->setNameProject($name_project);
            $changesDetected = true;
        }
        $description_project = $request->request->get('description_project');
        if ($description_project) {
            $project->setDescriptionProject($description_project);
            $changesDetected = true;
        }
        $status = $request->request->get('status');
        if ($status) {
            $project->setStatus($status);
            $changesDetected = true;
        }
        if (!$changesDetected) {
            return $this->json(['message' => 'No changes detected'], 400);
        }
        $errors = $validator->validate($project);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 500);
        }
        return $this->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ]);
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
