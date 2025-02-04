<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Group;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use App\Service\ApiResponseFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users')]
final class UserController extends AbstractController
{
    private ApiResponseFormatter $formatter;
    private GroupRepository $groupRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ApiResponseFormatter $formatter, GroupRepository $groupRepository, EntityManagerInterface $entityManager)
    {
        $this->formatter = $formatter;
        $this->groupRepository = $groupRepository;
        $this->entityManager = $entityManager;
    }

    #[Route(name: 'api_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        $userData = array_map(fn($user) => [
            'id'    => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'groups' => $user->getGroups()->map(fn(Group $group) => [
                'id'   => $group->getId(),
                'name' => $group->getName(),
            ])->toArray(),
        ], $users);

        return $this->formatter->success($userData, 'User list retrieved');
    }

    #[Route('/new', name: 'app_user_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]  # Only admins can create users
    public function new(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->formatter->error('Invalid data', 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));

        if (!empty($data['groups'])) {
            foreach ($data['groups'] as $groupId) {
                $group = $this->groupRepository->find($groupId);
                if ($group) {
                    $user->addGroup($group);
                }
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->formatter->success(['id' => $user->getId()], 'User created', 201);
    }

    #[Route('/{id<\d+>}', name: 'app_user_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(User $user): Response
    {
        // Check if the user is trying to access their own data or if they have admin privileges
        if ($this->getUser() !== $user && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedException('You do not have permission to view this user.');
        }

        return $this->formatter->success([
            'id'    => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'groups' => $user->getGroups()->map(fn(Group $group) => [
                'id'   => $group->getId(),
                'name' => $group->getName(),
            ])->toArray(),
        ], 'User details retrieved');
    }

    #[Route('/{id}/edit', name: 'api_user_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user): Response
    {
        // Check if the logged-in user has permission to edit this user
        if ($this->getUser() !== $user && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedException('You do not have permission to edit this user.');
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->formatter->error('Invalid request data', 400);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        }

        if (isset($data['groups'])) {
            $user->clearGroups(); // Ensure you have this method in the User entity
            foreach ($data['groups'] as $groupId) {
                $group = $this->groupRepository->find($groupId);
                if ($group) {
                    $user->addGroup($group);
                }
            }
        }

        $this->entityManager->flush();

        return $this->formatter->success(['id' => $user->getId()], 'User updated');
    }

    #[Route('/{id}/groups/{groupId}', name: 'api_user_add_group', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addUserToGroup(
        int $id,             // Pass the user id as an integer
        int $groupId,        // Group id
        UserRepository $userRepository,
        GroupRepository $groupRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->formatter->error('User not found', 404);
        }

        $group = $groupRepository->find($groupId);
        if (!$group) {
            return $this->formatter->error('Group not found', 404);
        }

        // Add group to the user
        $user->addGroup($group);
        $entityManager->flush();

        return $this->formatter->success([], 'User added to group successfully');
    }

    #[Route('/{id}/groups/{groupId}', name: 'api_user_remove_group', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeGroup(User $user, int $groupId): Response
    {
        $group = $this->groupRepository->find($groupId);

        if (!$group) {
            return $this->formatter->error('Group not found', 404);
        }

        $user->removeGroup($group);
        $this->entityManager->flush();

        return $this->formatter->success([], 'User removed from group');
    }

    #[Route('/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(User $user): Response
    {
        // Check if the logged-in user is authorized to delete this user
        if ($this->getUser() !== $user && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedException('You do not have permission to delete this user.');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->formatter->success([], 'User deleted', 204);
    }
}
