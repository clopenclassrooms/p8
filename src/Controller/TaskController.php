<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction(TaskRepository $taskRepository)
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findByisDone(false)]);
    }

    /**
     * @Route("/tasksDone", name="taskDone_list")
     */
    public function listDoneAction(TaskRepository $taskRepository)
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findByisDone(true)]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(TaskRepository $taskRepository, Request $request, EntityManagerInterface $objectManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($this->getUser() == Null)
            {
                $taskRepository->addAnonymousUser($task);

            }else
            {
                $task->setUser($this->getUser());
            }
            
            $objectManager->persist($task);
            $objectManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function editAction($id, TaskRepository $taskRepository, Request $request, EntityManagerInterface $objectManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $task = $taskRepository->find($id);
        $task = $taskRepository->addAnonymousUserIfNeeded($task);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objectManager->persist($task);
            $objectManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction($id, UserRepository $userRepository, EntityManagerInterface $objectManager, TaskRepository $taskRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $task = $taskRepository->find($id);
        $task = $taskRepository->addAnonymousUserIfNeeded($task);
        $userOfTheTask = $userRepository->find($task->getUser());
        if (
            $userOfTheTask === $this->getUser() or 
            in_array("ROLE_ADMIN",$this->getUser()->getRoles())
            )
        {
            $task->toggle(!$task->isDone());
            $objectManager->persist($task);
            $objectManager->flush();
            $this->addFlash('success', sprintf('La tâche %s a bien modifier.', $task->getTitle()));
        }else{
            $this->addFlash('error', sprintf('Vous pouvez uniquement modifier une tâche qui vous appartient', $task->getTitle()));
        }

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction($id,UserRepository $userRepository, TaskRepository $taskRepository, Request $request, EntityManagerInterface $objectManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $task = $taskRepository->find($id);
        $task = $taskRepository->addAnonymousUserIfNeeded($task);
        if (
            ($task->getUser() == $this->getUser()) or
            ($this->isGranted('ROLE_ADMIN'))
           )
        {
            $objectManager->remove($task);
            $objectManager->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
        }else{
            $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas supprimer une tâche créé par un autre utilisateur.');
        }


        return $this->redirectToRoute('task_list');
    }
}
