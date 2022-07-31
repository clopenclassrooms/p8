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
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findAll()]);
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
    public function toggleTaskAction()
    {
        $task = new Task;
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction($id,UserRepository $userRepository, TaskRepository $taskRepository, Request $request, EntityManagerInterface $objectManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $task = $taskRepository->find($id);
        if (
            ($task->getUser() == $this->getUser()) or
            (
                (
                    ( $task->getUser() == $userRepository->findOneByUsername('anonymous') ) or
                    ( $task->getUser() === null ) 
                ) and
                $this->isGranted('ROLE_ADMIN')
            )
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
