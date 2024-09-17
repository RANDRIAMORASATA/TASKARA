<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $users = $userRepository->findAllUsers();
        return $this->json(['users' => $users]);
    }

    #[Route('/user/{_id_user}', name: 'get_user', methods: ['GET'])]
    public function getOneUser(string $_id_user, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneByIdUser($_id_user);
        if (!$user) {
            return $this->json('No user found for id ' . $_id_user, 404);
        }
        return $this->json(['user' => $user]);
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
        // Log all request parameters
        $data = $request->request->all();
        error_log("Request data: " . json_encode($data));

        $user = new User();
        $id_user = $request->request->get('_id_user', uniqid());
        $user->setIdUser($id_user);

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
                $user->addProjects($project);
            }
        }

        // Process tasks
        foreach ($taskIds as $taskId) {
            $task = $taskRepository->find($taskId);
            if ($task) {
                $user->addTasks($task);
            }
        }

        $name_user = $request->request->get('name_user');
        error_log('Received name_user: ' . $name_user);
        if (empty($name_user)) {
            return $this->json(['error' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }
        $user->setNameUser($name_user);

        $email = $request->request->get('email');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Valid email is required'], Response::HTTP_BAD_REQUEST);
        }
        $user->setEmail($email);

        $mdp = $request->request->get('mdp');
        if (empty($mdp)) {
            return $this->json(['error' => 'Password is required'], Response::HTTP_BAD_REQUEST);
        }

        // Hash the password
        $confirm_mdp = $request->request->get('confirm_mdp');
        if ($mdp !== $confirm_mdp) {
            return $this->json(['error' => 'Passwords do not match'], Response::HTTP_BAD_REQUEST);
        }
        $user->setPassword($passwordHasher->hashPassword($user, $mdp));

        $infos_user = $request->request->get('infos_user');
        $user->setInfos_user($infos_user);

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
            'user' => $user
        ], Response::HTTP_CREATED);
    }





    #[Route('/user/{_id_user}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(
        string $_id_user,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        // Log the received request data
        $requestData = $request->request->all();
        error_log("Received request data: " . json_encode($requestData));

        // Retrieve the user from repository
        $user = $userRepository->findOneByIdUser($_id_user);
        if (!$user) {
            return $this->json('No user found for _id_user ' . $_id_user, 404);
        }

        // Initialize a flag to track changes
        $changesDetected = false;

        // Update user fields if new values are provided
        $nameUser = $request->request->get('name_user');
        error_log($nameUser);
        $email = $request->request->get('email');
        error_log($email);
        $newPassword = $request->request->get('mdp');
        error_log($newPassword);
        $confirmPassword = $request->request->get('confirm_mdp');
        error_log($newPassword);
        $infosUser = $request->request->get('infos_user');
        error_log($newPassword);

        if ($nameUser !== null && $user->getNameUser() !== $nameUser && $user->getInfos_user() !== $infosUser) {
            $user->setNameUser($nameUser);
            $changesDetected = true;
        }

        if ($email !== null && $user->getEmail() !== $email && $user->getInfos_user() !== $infosUser) {
            $user->setEmail($email);
            $changesDetected = true;
        }

        if ($newPassword !== null) {
            // Assuming you have a method to check if the password is different
            // This is pseudo-code and needs to be replaced with your actual logic
            if (!$passwordHasher->isPasswordValid($user, $newPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                if ($newPassword !== $confirmPassword) {
                    return $this->json(['error' => 'Passwords do not match'], Response::HTTP_BAD_REQUEST);
                }
                $user->setPassword($hashedPassword);
                $changesDetected = true;
            }
        }

        // If no changes were detected, return early
        if (!$changesDetected) {
            return $this->json([
                'message' => 'No changes detected. User not updated.'
            ]);
        }

        // Validate the updated user entity
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // Save changes
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while updating the user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return success response
        return $this->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }


    #[Route('/user/{_id_user}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(
        string $_id_user,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->findOneByIdUser($_id_user);
        if (!$user) {
            return $this->json('No user found for id' . $_id_user, 404);
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
