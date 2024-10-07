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
        $userData = array_map(function ($user) {
            return [
                'idUser' => $user->getIdUser(),
                'nameUser' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'infos_user' => $user->getInfos_user(), // Assurez-vous d'inclure ce champ
                '_id_user' => $user->getIdUser(),
                'roles' => $user->getRoles(),
                'salt' => $user->getSalt(),
                'userIdentifier' => $user->getUserIdentifier()
            ];
        }, $users);
        return $this->json(['users' => $userData]);
    }

    #[Route('/user/{_id_user}', name: 'get_user', methods: ['GET'])]
    public function getOneUser(string $_id_user, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneByIdUser($_id_user);
        if (!$user) {
            return $this->json('No user found for id ' . $_id_user, 404);
        }
        return $this->json([
            'user' => [
                'id_user' => $user->getIdUser(),
                'name_user' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'infos_user' => $user->getInfos_user(), // Assurez-vous d'inclure ce champ
                '_id_user' => $user->getIdUser(),
                'roles' => $user->getRoles(),
                'salt' => $user->getSalt(),
                'userIdentifier' => $user->getUserIdentifier()
            ]
        ]);
    }


    #[Route('/user/email/{email}', name: 'get_user_by_email', methods: ['GET'])]
    public function getUserByEmail(string $email, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneByEmailUser($email);
        if (!$user) {
            return $this->json('No user found for email ' . $email, 404);
        }
        return $this->json([
            'user' => [
                'id_user' => $user->getIdUser(),
                'name_user' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'infos_user' => $user->getInfos_user(), // Assurez-vous d'inclure ce champ
                '_id_user' => $user->getIdUser(),
                'roles' => $user->getRoles(),
                'salt' => $user->getSalt(),
                'userIdentifier' => $user->getUserIdentifier()
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
        $id_user = $data['_id_user'] ?? uniqid(); // Handle missing _id_user
        $user->setIdUser($id_user);

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
        $mdp = $data['mdp'] ?? null;
        if (empty($mdp)) {
            return $this->json(['error' => 'Password is required'], Response::HTTP_BAD_REQUEST);
        }
        $confirm_mdp = $data['confirm_mdp'] ?? null;
        if ($mdp !== $confirm_mdp) {
            return $this->json(['error' => 'Passwords do not match'], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $mdp));

        $infos_user = $data['infos_user'] ?? null;
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
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }
        // Log all request parameters
        error_log("Request data: " . json_encode($data));

        // Retrieve the user from repository
        $user = $userRepository->findOneByIdUser($_id_user);
        if (!$user) {
            return $this->json('No user found for _id_user ' . $_id_user, 404);
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
                'id_user' => $user->getIdUser(),
                'name_user' => $user->getNameUser(),
                'email' => $user->getEmail(),
                'infos_user' => $user->getInfos_user(), // Assurez-vous d'inclure ce champ
                '_id_user' => $user->getIdUser(),
                'roles' => $user->getRoles(),
                'salt' => $user->getSalt(),
                'userIdentifier' => $user->getUserIdentifier()
            ]
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
