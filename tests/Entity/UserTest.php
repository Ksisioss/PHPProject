<?php
// tests/Entity/UserTest.php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = new User();

        $user->setId(1);
        $this->assertEquals(1, $user->getId());

        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());

        $user->setFirstName('John');
        $this->assertEquals('John', $user->getFirstName());

        $user->setLastName('Doe');
        $this->assertEquals('Doe', $user->getLastName());

        $password = 'test_password';
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());

        $roles = ['ROLE_ADMIN', 'ROLE_USER'];
        $user->setRoles($roles);
        $this->assertEquals($roles, $user->getRoles());
    }

}
