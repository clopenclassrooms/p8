<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    private $userRepository;

    public function __construct(ManagerRegistry $registry,UserRepository $userRepository)
    {
        parent::__construct($registry, Task::class);
        $this->userRepository = $userRepository;
    }

    public function addAnonymousUserIfNeeded(Task $task)
    {
        if ($task->getUser() == null)
        {
            $task = $this->addAnonymousUser($task);
        }
        return $task;
    }

    public function addAnonymousUser(Task $task)
    {
        $anonyme = $this->userRepository->findOneByUsername('Anonyme');
        //check if user anonyme exist and if not, create it.
        if ( !$anonyme)
        {
            $anonyme = new User;
            $anonyme->setUsername('Anonyme');
            $anonyme->setPassword('');
            $anonyme->setEmail('');
            $anonyme->setRoles(["ROLE_USER"]);
        }
        $task->setUser($anonyme);
        return $task;
    }
}