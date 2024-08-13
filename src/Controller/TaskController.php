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
         return $this->json([
            'tasks' => $tasks
         ]);
    }
#[Route('/task/{_id_task}', name: 'get_one_task', methods: ['GET'])]
public function getOneTask(string $_id_task, TaskRepository $taskRepository): Response
{
    $task = $taskRepository->findOneByIdTask($_id_task);
    if(!$task){
        return $this->json(['message'=>'Task not found'],404);
    }
     return $this->json([
        'task' => $task
     ]);
}

    #[Route('/task', name: 'create_task', methods: ['POST'])]
    public function createTask(
        Request $request, 
        EntityManagerInterface $entityManager,
        TaskRepository $taskRepository,
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator

        ): Response
    {
         $data = $request->request->all();
        error_log("Request data: " . json_encode($data));

        if ($request->getContentType() === 'json') {
            $jsonData = json_decode($request->getContent(), true);
            error_log("JSON Request data: " . json_encode($jsonData));

        }

        $task = new Task();
        $id_task = $request->request->get('_id_task', uniqid());
        $task->setIdTask($id_task);
        $projectId = $data['_project_id'] ?? null;
        error_log("project ID from request: " . $projectId);

        if (empty($projectId)) {
            return $this->json(['message' => 'Project ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $project = $projectRepository->find($projectId);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }
        $task->setUser($project);
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
        $task->setUser($user);

        if(empty($request->request->get('name_task'))|| empty($request->request->get('description_task')) || empty($request->request->get('status'))){
            return $this->json(['message'=>'Name task is required'],400);
        }
        $task->setNameTask($request->request->get('name_task'));
        $task->setDescriptionTask($request->request->get('description_task'));
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setStatus($request->request->get('status'));
        $projectId = $request->request->get('_id_project');
        $userId = $request->request->get('_id_user');
        $project = $this->$projectRepository->findOneByIdProject($projectId);
        $user = $this->$userRepository->findOneByIdUser($userId);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        $task->setProject($project);
        $task->setUser($user);

        $errors = $validator->validate($task);
        if(count($errors) > 0){
            return $this->json($errors,400);
        }
        try {
            $saveResult = $taskRepository->saveTask($task);
            if(!$saveResult){
                throw new \Exception('An error occurred while creating the task');
            }
            
        } catch (\Exception $e) {
            return $this->json(['error'=>'An error occured while creating the task'],500);
        }
        return $this->json([
            'message' => 'Task created successfully',
            'task' => $task
        ], Response::HTTP_CREATED);
        


    }

    #[Route('/task/{_id_task}', name: 'update_task', methods: ['PUT'])]
    public function updateTask(
        string $_id_task, 
        Request $request, 
        TaskRepository $taskRepository, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response
    {
        $requestData = $request->request->all();
        error_log("Request data: " . json_encode($requestData));
    

        $task = $taskRepository->findOneByIdTask($_id_task);
        if(!$task){
            return $this->json('No task found for _id_user ' . $_id_task, 404);
        }
        
        $changedDetected = false;
        $nameTask = $request->request->get('name_task');
        error_log("Name Task: " . $nameTask);
        $descriptionTask = $request->request->get('description_task');
        error_log("Description Task: " . $descriptionTask);
        $status = $request->request->get('status');
        error_log("Status: " . $status);

        if($nameTask !== null && $task->getNameTask() !== $nameTask){
            $task->setNameTask($nameTask);
            $changedDetected = true;
        }
        if($descriptionTask !== null && $task->getDescriptionTask() !== $descriptionTask){
            $task->setDescriptionTask($descriptionTask);
            $changedDetected = true;
        }
        if($status !== null && $task->getStatus() !== $status){
            $task->setStatus($status);
            $changedDetected = true;
        }
        if(!$changedDetected){
            return $this->json(['message'=>'No changes detected'],400);
        }
        $errors = $validator->validate($task);
        if(count($errors) > 0){
            return $this->json($errors,400);
        }
        //Save the updated task
        try {
            $entityManager->flush();
            
        } catch (\Exception $e) {
            return $this->json(['error'=>'An error occured while updating the task'],500);
        }
        return $this->json([
            'message' => 'Task updated successfully',
            'task' => $task
        ]);
    }

    #[Route('/task/{_id_task}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(string $_id_task, TaskRepository $taskRepository, EntityManagerInterface $entityManager): Response
    {
        $task = $taskRepository->findOneByIdTask($_id_task);
        if(!$task){
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

