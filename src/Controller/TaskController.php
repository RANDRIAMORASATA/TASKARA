<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
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
                'isUrgent' => $task->isUrgent(),
                '_id_project' => $task->getProject()->getIdProject(),
                '_user_id' => $task->getUser()->getUserId(),
            ];
        }, $tasks);
        return $this->json(['tasks' => $formattedTasks]);
    }

    #[Route('/task/{user_id}', name: 'get_tasks_by_user', methods: ['GET'])]
    public function getTasksByUser(string $user_id, TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAllTaskByUser($user_id);
        if (empty($tasks)) {
            return $this->json(['message' => 'No tasks found for this user'], Response::HTTP_NOT_FOUND);
        }

        // Format tasks
        $formattedTasks = array_map(function ($task) {
            return [
                '_id_task' => $task->getIdTask(),
                'name_task' => $task->getNameTask(),
                'description_task' => $task->getDescriptionTask(),
                'createdAt' => $task->getCreatedAt() ? $task->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $task->getUpdatedAt() ? $task->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'status' => $task->getStatus(),
                'isUrgent' => $task->isUrgent(),
                '_id_project' => $task->getProject()->getIdProject(),
                '_user_id' => $task->getUser()->getUserId(), // Utilisez 'getUserId()' pour récupérer _user_id
            ];
        }, $tasks);

        return $this->json(['tasks' => $formattedTasks], Response::HTTP_OK);
    }

    #[Route('/task/urgent/{user_id}', name: 'get_urgent_tasks_by_user', methods: ['GET'])]
    public function getUrgentTasksByUser(string $user_id, TaskRepository $taskRepository): Response
    {
        // Fetch tasks for the user that are marked as urgent
        $tasks = $taskRepository->findUrgentTasksByUser($user_id);

        if (empty($tasks)) {
            return $this->json(['message' => 'No urgent tasks found for this user'], Response::HTTP_NOT_FOUND);
        }

        // Format the tasks to match the expected response structure
        $formattedTasks = array_map(function ($task) {
            return [
                '_id_task' => $task->getIdTask(),
                'name_task' => $task->getNameTask(),
                'description_task' => $task->getDescriptionTask(),
                'createdAt' => $task->getCreatedAt() ? $task->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $task->getUpdatedAt() ? $task->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'status' => $task->getStatus(),
                'isUrgent' => $task->isUrgent(),
                '_id_project' => $task->getProject()->getIdProject(),
                '_user_id' => $task->getUser()->getUserId(),
            ];
        }, $tasks);

        return $this->json(['tasks' => $formattedTasks], Response::HTTP_OK);
    }

    #[Route('/task', name: 'create_task', methods: ['POST'])]
    public function createTask(
        Request $request,
        TaskRepository $taskRepository,
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        // Create Task entity
        $task = new Task();
        $id_task = $data['_id_task'] ?? uniqid();
        $task->setIdTask($id_task);

        // Validate name and description
        if (empty($data['name_task'])) {
            return $this->json(['message' => 'Task name is required'], Response::HTTP_BAD_REQUEST);
        }
        $task->setNameTask($data['name_task']);

        if (empty($data['description_task'])) {
            return $this->json(['message' => 'Task description is required'], Response::HTTP_BAD_REQUEST);
        }
        $task->setDescriptionTask($data['description_task']);

        $task->setStatus($data['status'] ?? null);
        $task->setIsUrgent($task->getStatus() === 'Urgent');
        $task->setCreatedAt(new \DateTimeImmutable());

        // Validate Project
        if (empty($data['_id_project'])) {
            return $this->json(['message' => 'Project ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $project = $projectRepository->find($data['_id_project']);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }
        $task->setProject($project);

        // Validate User
        if (empty($data['_user_id'])) {
            return $this->json(['message' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['_user_id']);
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $task->setUser($user);

        // Validate task object
        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // Persist task
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
                'status' => $task->getStatus(),
                'isUrgent' => $task->isUrgent(),
                '_id_project' => $task->getProject()->getIdProject(),
                '_user_id' => $task->getUser()->getUserId(),
            ]
        ], Response::HTTP_CREATED);
    }



    #[Route('/task/{id_task}', name: 'update_task', methods: ['PUT'])]
    public function updateTask(Request $request, TaskRepository $taskRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Decode request data
        $id_task = $request->attributes->get('id_task');
        $data = $request->getContentType() === 'json' ? json_decode($request->getContent(), true) : $request->request->all();

        $task = $taskRepository->findOneByIdTask($id_task);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $changesDetected = false;

        // Update task name
        if (isset($data['name_task']) && $task->getNameTask() !== $data['name_task']) {
            $task->setNameTask($data['name_task']);
            $changesDetected = true;
        }

        // Update task description
        if (isset($data['description_task']) && $task->getDescriptionTask() !== $data['description_task']) {
            $task->setDescriptionTask($data['description_task']);
            $changesDetected = true;
        }

        // Update task status and urgency
        if (isset($data['status']) && $task->getStatus() !== $data['status']) {
            $task->setStatus($data['status']);
            $task->setIsUrgent($data['status'] === 'Urgent');
            $changesDetected = true;
        }

        // If no changes detected
        if (!$changesDetected) {
            return $this->json(['message' => 'No changes detected'], Response::HTTP_BAD_REQUEST);
        }

        // Validate task
        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return $this->json(['errors' => array_map(fn($e) => $e->getMessage(), iterator_to_array($errors))], Response::HTTP_BAD_REQUEST);
        }

        // Persist changes
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return response
        return $this->json([
            'message' => 'Task updated successfully',
            'task' => [
                '_id_task' => $task->getIdTask(),
                'name_task' => $task->getNameTask(),
                'description_task' => $task->getDescriptionTask(),
                'createdAt' => $task->getCreatedAt() ? $task->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $task->getUpdatedAt() ? $task->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'status' => $task->getStatus(),
                'isUrgent' => $task->isUrgent(),
                '_id_project' => $task->getProject()->getIdProject(),
                '_user_id' => $task->getUser()->getUserId(),
            ]
        ], Response::HTTP_OK);
    }



    #[Route('/task/{_id_task}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(
        string $_id_task,
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $task = $taskRepository->findOneByIdTask($_id_task);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], 404);
        }

        try {
            $entityManager->remove($task);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 500);
        }

        return $this->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}
