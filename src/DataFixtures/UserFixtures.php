<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        $groupAdmin = new Group();
        $groupAdmin->setName('Admins');
        $manager->persist($groupAdmin);

        $groupUser = new Group();
        $groupUser->setName('Users');
        $manager->persist($groupUser);


        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@example.com");


            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);


            $user->setRoles(['ROLE_USER']);
            $user->addGroup($groupUser);

            $manager->persist($user);
        }

        $admin = new User();
        $admin->setEmail('admin@example.com');

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'adminpassword');
        $admin->setPassword($hashedPassword);

        $admin->setRoles(['ROLE_ADMIN']);
        $admin->addGroup($groupAdmin);

        $manager->persist($admin);

        $manager->flush();
    }
}
