<?php

namespace App\Tests;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
    /** @test */
    public function userTests(): void
    {
        self::bootKernel();

        $task = new Task;
        $task->setTitle("Title");
        $task->setContent("Content");

        $userTest = new User;
        $userTest->setUsername("userTest");
        $userTest->setPassword("userTest");
        $userTest->setEmail("userTest@userTest.userTest");
        $userTest->setRoles(["ROLE_USER"]);
        $userTest->addTask($task);
        $this->entityManager->persist($userTest);
        $this->entityManager->flush();

        $userTest = $this->entityManager
        ->getRepository(User::class)
        ->findOneByUsername("userTest");


        //$this->assertIsInt($userTest->getId());
        $this->assertEquals("userTest",$userTest->getUsername());
        $this->assertEquals("userTest@userTest.userTest",$userTest->getEmail());
        $this->assertEquals("userTest",$userTest->getPassword());
        $this->assertEquals(["ROLE_USER"],$userTest->getRoles());
        $this->assertEquals($task,$userTest->getTasks()[0]);
        $userTest->removeTask($task);
        $this->assertEquals(0,$userTest->getTasks()->count());
    }
}
