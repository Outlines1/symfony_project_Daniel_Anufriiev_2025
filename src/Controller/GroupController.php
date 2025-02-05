<?php

namespace App\Controller;

use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use App\Service\ApiResponseFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/groups')]
final class GroupController extends AbstractController
{
    private ApiResponseFormatter $formatter;
    private GroupRepository $groupRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ApiResponseFormatter $formatter,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->formatter = $formatter;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route(name: 'api_group_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $groups = $this->groupRepository->findAll();

        $groupData = array_map(fn($group) => [
            'id'    => $group->getId(),
            'name'  => $group->getName(),
            'roles' => $group->getRoles(),
            'users' => $group->getUsers()->map(fn($user) => [
                'id'    => $user->getId(),
                'email' => $user->getEmail(),
            ])->toArray(),
        ], $groups);

        return $this->formatter->success($groupData, 'Group list retrieved');
    }

    #[Route('/new', name: 'api_group_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]  // Only admin can create groups
    public function new(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'])) {
            return $this->formatter->error('Invalid data', 400);
        }

        $group = new Group();
        $group->setName($data['name']);

        if (!empty($data['roles'])) {
            $group->setRoles($data['roles']);
        }

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        return $this->formatter->success(['id' => $group->getId()], 'Group created', 201);
    }

    #[Route('/{id<\d+>}', name: 'api_group_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Group $group): Response
    {
        return $this->formatter->success([
            'id'    => $group->getId(),
            'name'  => $group->getName(),
            'roles' => $group->getRoles(),
            'users' => $group->getUsers()->map(fn($user) => [
                'id'    => $user->getId(),
                'email' => $user->getEmail(),
            ])->toArray(),
        ], 'Group details retrieved');
    }

    #[Route('/{id}/edit', name: 'api_group_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]  // Only admin can edit groups
    public function edit(Request $request, Group $group): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->formatter->error('Invalid request data', 400);
        }

        if (isset($data['name'])) {
            $group->setName($data['name']);
        }
        if (isset($data['roles'])) {
            $group->setRoles($data['roles']);
        }

        $this->entityManager->flush();

        return $this->formatter->success(['id' => $group->getId()], 'Group updated');
    }

    #[Route('/{id}/delete', name: 'api_group_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]  // Only admin can delete groups
    public function delete(Group $group): Response
    {
        $this->entityManager->remove($group);
        $this->entityManager->flush();

        return $this->formatter->success([], 'Group deleted', 204);
    }

    #[Route('/{id}/users/{userId}', name: 'api_group_add_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]  // Only admin can add users to groups
    public function addUserToGroup(int $id, int $userId): Response
    {
        $group = $this->groupRepository->find($id);
        if (!$group) {
            return $this->formatter->error('Group not found', 404);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->formatter->error('User not found', 404);
        }

        $group->addUser($user);
        $this->entityManager->flush();

        return $this->formatter->success([], 'User added to group');
    }

    #[Route('/{id}/users/{userId}', name: 'api_group_remove_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeUserFromGroup(int $id, int $userId): Response
    {
        $group = $this->groupRepository->find($id);
        if (!$group) {
            return $this->formatter->error('Group not found', 404);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->formatter->error('User not found', 404);
        }

        $group->removeUser($user);
        $this->entityManager->flush();

        return $this->formatter->success([], 'User removed from group');
    }
}
