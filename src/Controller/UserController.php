<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'get_users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs avec leurs projets et tâches associés
        $users = $userRepository->findAllUsers();

        // Vérifier si des utilisateurs sont trouvés
        if (!$users) {
            return $this->json(['error' => 'No users found'], 404);
        }

        // Convertir les collections en tableaux
        $userData = array_map(function ($user) {
            // Convertir les collections ArrayCollection en tableaux
            $projects = $user->getProjects() ? $user->getProjects()->toArray() : [];
            $tasks = $user->getTasks() ? $user->getTasks()->toArray() : [];

            return [
                '_id_user' => $user->getUserId(),
                'name_user' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'image_link' => $user->getImage_link(),
                'role' => $user->getRole(),
                'adress' => $user->getAdress(),
                'contract' => $user->getContract(),
                'infos_user' => $user->getInfos_user(),
                // Assurez-vous que les projets et tâches sont bien extraits
                'projects' => array_map(fn($project) => [
                    '_id_project' => $project->getIdProject(),
                    'name_project' => $project->getNameProject(),
                ], $projects),
                'tasks' => array_map(fn($task) => [
                    '_id_task' => $task->getIdTask(),
                    'name_task' => $task->getNameTask(),
                ], $tasks),
            ];
        }, $users);

        // Retourner les utilisateurs sous forme de JSON
        return $this->json(['users' => $userData]);
    }

    #[Route('/user/email/{email}', name: 'get_user_by_email', methods: ['GET'])]
    public function getUserDataByEmail(string $email, UserRepository $userRepository): Response
    {
        // Recherche de l'utilisateur par son email
        $user = $userRepository->findOneByEmailUser($email);

        // Si l'utilisateur n'existe pas, renvoyer un message d'erreur
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        // Récupérer les projets et les tâches associés
        $projects = $user->getProjects() ? $user->getProjects()->toArray() : [];
        $tasks = $user->getTasks() ? $user->getTasks()->toArray() : [];

        // Structurer la réponse avec les informations de l'utilisateur, ses projets et tâches
        $userData = [
            '_id_user' => $user->getUserId(),
            'name_user' => $user->getNameUser(),
            'email' => $user->getEmail(),
            'image_link' => $user->getImage_link(),
            'role' => $user->getRole(),
            'adress' => $user->getAdress(),
            'contract' => $user->getContract(),
            'infos_user' => $user->getInfos_user(),
            // Projets associés
            'projects' => array_map(fn($project) => [
                '_id_project' => $project->getIdProject(),
                'name_project' => $project->getNameProject(),
            ], $projects),
            // Tâches associées
            'tasks' => array_map(fn($task) => [
                '_id_task' => $task->getIdTask(),
                'name_task' => $task->getNameTask(),
            ], $tasks),
        ];

        // Retourner les données de l'utilisateur
        return $this->json(['user' => $userData]);
    }


    #[Route('/user/{_user_id}', name: 'get_user', methods: ['GET'])]
    public function getOneUser(string $_user_id, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneByIdUser($_user_id);
        if (!$user) {
            return $this->json('No user found for id ' . $_user_id, 404);
        }
        $projects = $user->getProjects() ? $user->getProjects()->toArray() : [];
        $tasks = $user->getTasks() ? $user->getTasks()->toArray() : [];

        return $this->json([
            'user' => [
                '_id_user' => $user->getUserId(),
                'name_user' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'image_link' => $user->getImage_link(),
                'role' => $user->getRole(),
                'adress' => $user->getAdress(),
                'contract' => $user->getContract(),
                'infos_user' => $user->getInfos_user(),
                'projects' => array_map(fn($project) => [
                    '_id_project' => $project->getIdProject(),
                    'name_project' => $project->getNameProject(),
                ], $projects),
                'tasks' => array_map(fn($task) => [
                    '_id_task' => $task->getIdTask(),
                    'name_task' => $task->getNameTask(),
                ], $tasks),
            ]
        ]);
    }


    /**User create */

    #[Route('/user', name: 'create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        UserRepository $userRepository,
        ProjectRepository $projectRepository,
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }
        // Log all request parameters
        error_log("Request data: " . json_encode($data));

        $user = new User();
        $id_user = $data['_user_id'] ?? uniqid();
        $user->setUserId($id_user);

        $name_user = $data['name_user'] ?? null;
        if (empty($name_user)) {
            return $this->json(['error' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }
        $user->setNameUser($name_user);

        $email = $data['email'] ?? null;
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Valid email is required'], Response::HTTP_BAD_REQUEST);
        }
        $user->setEmail($email);

        // Gestion de l'image
        if ($request->files->has('image')) {
            $file = $request->files->get('image');
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->guessExtension() ?: 'jpg';
            $newFilename = sprintf('%s-%d.%s', $originalFilename, time(), $extension);
            $directory = $this->getParameter('kernel.project_dir') . '/assets/img/user_image';

            try {
                $file->move($directory, $newFilename);
                $user->setImage_link('/assets/img/user_image/' . $newFilename); // Set uploaded image link
            } catch (FileException $e) {
                error_log("File upload error: " . $e->getMessage());
                return new JsonResponse(['error' => 'Could not upload file.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            // Set default image if no image is uploaded
            $user->setImage_link('/assets/img/user_image/default.jpg'); // Replace with your default image path
        }

        $role = $data['role'] ?? " ";
        $user->setRole($role);

        $adress = $data['adress'] ?? " ";
        $user->setAdress($adress);


        $contract = $data['contract'] ?? " ";
        $user->setContract($contract);

        $mdp = $data['mdp'] ?? null;
        if (empty($mdp)) {
            return $this->json(['error' => 'Password is required'], Response::HTTP_BAD_REQUEST);
        }
        $confirm_mdp = $data['confirm_mdp'] ?? null;
        if ($mdp !== $confirm_mdp) {
            return $this->json(['error' => 'Passwords do not match'], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $mdp));

        $infos_user = $data['infos_user'] ?? " ";
        $user->setInfos_user($infos_user);

        // Handle projects and tasks
        $projectIds = $request->request->get('projects');
        $taskIds = $request->request->get('tasks');

        // Ensure these are arrays
        if (!is_array($projectIds)) {
            $projectIds = [];
        }

        if (!is_array($taskIds)) {
            $taskIds = [];
        }

        // Process projects
        foreach ($projectIds as $projectId) {
            $project = $projectRepository->find($projectId);
            if ($project) {
                $user->addProject($project);
            }
        }

        // Process tasks
        foreach ($taskIds as $taskId) {
            $task = $taskRepository->find($taskId);
            if ($task) {
                $user->addTask($task);
            }
        }


        // Validation
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
        }

        try {
            $userRepository->saveUser($user);
        } catch (\Exception $e) {
            error_log("Error: " . $e->getMessage());
            return $this->json(['error' => 'An error occurred while creating the user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'User created successfully',
            'user' =>  [
                '_id_user' => $user->getUserId(),
                'name_user' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'image_link' => $user->getImage_link(),
                'role' => $user->getRole(),
                'adress' => $user->getAdress(),
                'contract' => $user->getContract(),
                'infos_user' => $user->getInfos_user()
            ]
        ], Response::HTTP_CREATED);
    }

    /**User Update */
    #[Route('/user/{_user_id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(
        string $_user_id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        // Log the received request data
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }
        // Log all request parameters
        error_log("Request data: " . json_encode($data));

        // Retrieve the user from repository
        $user = $userRepository->findOneByIdUser($_user_id);
        if (!$user) {
            return $this->json('No user found for _user_id ' . $_user_id, 404);
        }

        // Initialize a flag to track changes
        $changesDetected = false;

        // Update user fields if new values are provided
        if (isset($data['name_user']) && $user->getNameUser() !== $data['name_user']) {
            $user->setNameUser($data['name_user']);
            $changesDetected = true;
        }


        if (isset($data['email']) && $user->getEmail() !== $data['email']) {
            $user->setEmail($data['email']);
            $changesDetected = true;
        }

        // Gestion de l'image pour mise à jour
        if ($request->files->has('image')) {
            error_log("Image file detected");
            $file = $request->files->get('image');
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->guessExtension() ?: 'jpg';
            $newFilename = sprintf('%s-%d.%s', $originalFilename, time(), $extension);

            $directory = $this->getParameter('kernel.project_dir') . '/assets/img/user_image/';

            try {
                $file->move($directory, $newFilename);
                $user->setImage_link('/assets/img/user_image/' . $newFilename); // Mettre à jour l'URL de l'image
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Could not upload file.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $existingImageLink = $user->getImage_link();
            if ($existingImageLink) {
                $user->setImage_link($existingImageLink);
                $changesDetected = true; // Optional: Only set this if you consider it a change
            }
        }

        if (isset($data['role']) && $user->getRole() !== $data['role']) {
            $user->setRole($data['role']);
            $changesDetected = true;
        }

        if (isset($data['adress']) && $user->getAdress() !== $data['adress']) {
            $user->setAdress($data['adress']);
            $changesDetected = true;
        }

        if (isset($data['contract']) && $user->getContract() !== $data['contract']) {
            $user->setContract($data['contract']);
            $changesDetected = true;
        }

        if (isset($data['mdp'])) {
            if ($data['mdp'] !== ($data['confirm_mdp'] ?? '')) {
                return $this->json(['error' => 'Passwords do not match'], Response::HTTP_BAD_REQUEST);
            }
            $user->setPassword($passwordHasher->hashPassword($user, $data['mdp']));
            $changesDetected = true;
        }

        if (isset($data['infos_user']) && $user->getInfos_user() !== $data['infos_user']) {
            $user->setInfos_user($data['infos_user']);
            $changesDetected = true;
        }

        // If no changes were detected, return early
        if (!$changesDetected) {
            return $this->json(['message' => 'No changes detected. User not updated.']);
        }

        // Validate the updated user entity
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
        }

        // Save changes
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return $this->json(['error' => 'An error occurred while updating the user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'User updated successfully',
            'user' => [
                '_id_user' => $user->getUserId(),
                'name_user' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'image_link' => $user->getImage_link(),
                'role' => $user->getRole(),
                'adress' => $user->getAdress(),
                'contract' => $user->getContract(),
                'infos_user' => $user->getInfos_user()
            ]
        ]);
    }



    #[Route('/user/{_user_id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(
        string $_user_id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->findOneByIdUser($_user_id);
        if (!$user) {
            return $this->json('No user found for id' . $_user_id, 404);
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occured while updating the user'], 500);
        }

        return $this->json(['message' => 'User deleted successfully']);
    }
}
