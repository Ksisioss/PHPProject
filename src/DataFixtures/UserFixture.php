<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create an admin user
        $adminUser = new User();
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('User');
        $adminUser->setEmail('admin@example.com');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword(
            $this->passwordHasher->hashPassword(
                $adminUser,
                'adminpassword'
            )
        );
        $manager->persist($adminUser);

        // Create a regular user
        $regularUser = new User();
        $regularUser->setFirstName('Regular');
        $regularUser->setLastName('User');
        $regularUser->setEmail('user@example.com');
        $regularUser->setRoles(['ROLE_USER']);
        $regularUser->setPassword(
            $this->passwordHasher->hashPassword(
                $regularUser,
                'userpassword'
            )
        );
        $manager->persist($regularUser);

        // Flush to save users to the database
        $manager->flush();
    }
}