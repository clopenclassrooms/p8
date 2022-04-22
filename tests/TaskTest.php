<?php

namespace App\Tests;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
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
    public function taskTests(): void
    {
        $taskTest = new Task;
        $date = new \DateTime();
        $taskTest->setCreatedAt($date);
        $taskTest->setTitle("taskTest");
        $taskTest->setContent("taskTest");
        $this->entityManager->persist($taskTest);
        $this->entityManager->flush();

        $taskTest = $this->entityManager
        ->getRepository(Task::class)
        ->findOneByTitle("taskTest");

        $this->assertIsInt($taskTest->getId());
        $this->assertEquals($date,$taskTest->getCreatedAt());
        $this->assertEquals("taskTest",$taskTest->getTitle());
        $this->assertEquals("taskTest",$taskTest->getContent());
        $this->assertEquals(false,$taskTest->isDone());
        $taskTest->toggle(true);
        $this->assertEquals(true,$taskTest->isDone());

    }
}
