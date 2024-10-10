<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController
{
    #[Route('/task', name: 'get_tasks', methods: ['GET'])]
    public function getTasks(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAllTasks();
        $formattedTasks = array_map(function ($task) {
            return [
                '_id_task' => $task->getIdTask(),
                'name_task' => $task->getNameTask(),
                'description_task' => $task->getDescriptionTask(),
                'createdAt' => $task->getCreatedAt(),
                'updatedAt' => $task->getUpdatedAt(),
                'status' => $task->getStatus(),
                'project' => $task->getProject()

            ];
        }, $tasks);
        return $this->json(['tasks' => $formattedTasks]);
    }
    #[Route('/task/{_id_task}', name: 'get_one_task', methods: ['GET'])]
    public function getOneTask(string $_id_task, TaskRepository $taskRepository): Response
    {
        $task = $taskRepository->findOneByIdTask($_id_task);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], 404);
        }
        $formattedTask = [
            '_id_task' => $task->getIdTask(),
            'name_task' => $task->getNameTask(),
            'description_task' => $task->getDescriptionTask(),
            'createdAt' => $task->getCreatedAt(),
            'updatedAt' => $task->getUpdatedAt(),
            'status' => $task->getStatus(),
            'project' => $task->getProject()

        ];
        return $this->json(['task' => $formattedTask]);
    }

    #[Route('/task', name: 'create_task', methods: ['POST'])]
    public function createTask(
        Request $request,
        EntityManagerInterface $entityManager,
        TaskRepository $taskRepository,
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): Response {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        error_log("Decoded JSON data: " . json_encode($data));
        $task = new Task();

        $id_task = $data['_id_task'] ?? uniqid();
        $task->setIdTask($id_task);

        $name_task = $data['name_task'] ?? null;
        if (empty($name_task)) {
            return $this->json(['message' => 'Task name is required'], Response::HTTP_BAD_REQUEST);
        }
        $task->setNameTask($name_task);

        $description_task = $data['description_task'] ?? null;
        if (empty($description_task)) {
            return $this->json(['message' => 'Task description is required'], Response::HTTP_BAD_REQUEST);
        }
        $task->setDescriptionTask($description_task);

        $status = $data['status'] ?? null;
        if (empty($status)) {
            return $this->json(['message' => 'Task status is required'], Response::HTTP_BAD_REQUEST);
        }
        $task->setStatus($status);

        $createdAt = new \DateTimeImmutable();
        $task->setCreatedAt($createdAt);

        // Retrieve the associated Project
        $projectId = $data['project'] ?? null;
        if ($projectId) {
            $project = $projectRepository->find($projectId);
            if (!$project) {
                return $this->json(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
            }
            $task->setProject($project);
        }

        // Retrieve the associated User
        $userId = $data['_user_id'] ?? null;
        if (empty($userId)) {
            return $this->json(['message' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $task->setUser($user);

        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($task);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while creating the task: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Task created successfully',
            'task' => [
                '_id_task' => $task->getIdTask(),
                'name_task' => $task->getNameTask(),
                'description_task' => $task->getDescriptionTask(),
                'createdAt' => $task->getCreatedAt()->format(DATE_ISO8601),
                'updatedAt' => $task->getUpdatedAt() ? $task->getUpdatedAt()->format(DATE_ISO8601) : null,
                'status' => $task->getStatus(),
                'project' => $task->getProject()
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/task/{_id_task}', name: 'update_task', methods: ['PUT'])]
    public function updateTask(
        string $_id_task,
        Request $request,
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        error_log("Request data: " . json_encode($data));

        $task = $taskRepository->findOneByIdTask($_id_task);
        if (!$task) {
            return $this->json(['message' => 'No task found for _id_task ' . $_id_task], Response::HTTP_NOT_FOUND);
        }

        $changedDetected = false;

        $name_task = $data['name_task'] ?? null;
        if ($name_task !== null && $task->getNameTask() !== $name_task) {
            $task->setNameTask($name_task);
            $changedDetected = true;
        }

        $description_task = $data['description_task'] ?? null;
        if ($description_task !== null && $task->getDescriptionTask() !== $description_task) {
            $task->setDescriptionTask($description_task);
            $changedDetected = true;
        }

        $status = $data['status'] ?? null;
        if ($status !== null && $task->getStatus() !== $status) {
            $task->setStatus($status);
            $changedDetected = true;
        }

        if (!$changedDetected) {
            return $this->json(['message' => 'No changes detected'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // Save the updated task
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while updating the task: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Task updated successfully',
            'task' => [
                '_id_task' => $task->getIdTask(),
                'name_task' => $task->getNameTask(),
                'description_task' => $task->getDescriptionTask(),
                'createdAt' => $task->getCreatedAt()->format(DATE_ISO8601),
                'updatedAt' => $task->getUpdatedAt() ? $task->getUpdatedAt()->format(DATE_ISO8601) : null,
                'status' => $task->getStatus(),
                'project' => $task->getProject()
            ],
        ], Response::HTTP_OK);
    }


    #[Route('/task/{_id_task}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(string $_id_task, TaskRepository $taskRepository, EntityManagerInterface $entityManager): Response
    {
        $task = $taskRepository->findOneByIdTask($_id_task);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], 404);
        }
        try {
            $entityManager->remove($task);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while deleting the task'], 500);
        }

        return $this->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}
