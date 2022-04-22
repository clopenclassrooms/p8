<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $_manager;
    private $_passwordHasher;
    private $_userRepository;

    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $this->_passwordHasher = $passwordHasher;
        $this->_userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $this->_manager = $manager;
        $this->addUsers();
        $this->addTasks();
    }

    private function addTasks()
    {
        $users = $this->_userRepository->findAll();
        
        //Create a task for each User
        foreach($users as $user)
        {
            $task = new Task;
            $task->setCreatedAt(new DateTime());
            $task->setTitle("Tache de " . $user->getUsername());
            $task->setContent("Tache de " . $user->getUsername());
            $task->isDone(false);
            $task->setUser($user);
            $this->_manager->persist($task);
            $this->_manager->flush();
        }

        //Create a task without attach user
        $task = new Task;
        $task->setCreatedAt(new DateTime());
        $task->setTitle("Tache d'un utilisateur non connecté");
        $task->setContent("Tache d'un utilisateur non connecté");
        $task->isDone(false);
        $this->_manager->persist($task);
        $this->_manager->flush();

    }
    
    private function addUsers()
    {
        //admin
        $adminUser = new User;
        $password = $this->_passwordHasher->hashPassword($adminUser,"admin");
        $adminUser->setUsername("admin");
        $adminUser->setPassword($password);
        $adminUser->setEmail("admin@admin.admin");
        $adminUser->setRoles(["ROLE_ADMIN"]);
        $this->_manager->persist($adminUser);
        
        //anonymous user
        $anonymousUser = new User;
        $password = $this->_passwordHasher->hashPassword($anonymousUser,"anonymous");
        $anonymousUser->setUsername("anonymous");
        $anonymousUser->setPassword($password);
        $anonymousUser->setEmail("anonymous@anonymous.anonymous");
        $anonymousUser->setRoles(["ROLE_USER"]);
        $this->_manager->persist($anonymousUser);

        //user1
        $user1 = new User;
        $password = $this->_passwordHasher->hashPassword($user1,"user1");
        $user1->setUsername("user1");
        $user1->setPassword($password);
        $user1->setEmail("user1@user1.user1");
        $user1->setRoles(["ROLE_USER"]);
        $this->_manager->persist($user1);

        //user2
        $user2 = new User;
        $password = $this->_passwordHasher->hashPassword($user2,"user2");
        $user2->setUsername("user2");
        $user2->setPassword($password);
        $user2->setEmail("user2@user2.user2");
        $user2->setRoles(["ROLE_USER"]);
        $this->_manager->persist($user2);

        $this->_manager->flush();
    }
}
