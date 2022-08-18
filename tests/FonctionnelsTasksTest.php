<?php

namespace App\Tests;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FonctionnelsTasksTest extends WebTestCase
{
    private $_client;
    private $_crawler;

    public function testTaskCreation(): void
    {
        $this->init();
        $this->login('user1','user1');
        $this->loadCreateNewTask();
        $this->addOrEditTask('test create task with user1', 'test create task with user1', true);

        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche a été bien été ajoutée.")')->count());
        $this->assertSame(1, $this->_crawler->filter('html:contains("test create task with user1")')->count());               
    }
 
    public function testTaskCreationWithNotConnectUser(): void
    {
        $this->init();
        $this->loadHomePage();
        $link = $this->_crawler->selectLink('Créer une nouvelle tâche')->link();
        $this->_crawler = $this->_client->click($link);
        $this->_client->followRedirects();
        $this->assertResponseRedirects('http://localhost/login');              
    }

    public function testTaskEdition(): void
    {
        $this->init();
        $this->login('user1','user1');
        $this->loadEditTask('Tache de user1');
        $this->addOrEditTask('test create task with user1 (modified)','test create task with user1 (modified)', false);

        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche a bien été modifiée.")')->count());
        $this->assertSame(1, $this->_crawler->filter('html:contains("test create task with user1 (modified)")')->count());
    }

    public function testTaskAdminEditionForNotConnectedUser(): void
    {
        $this->init();
        $this->login('admin','admin');
        $this->loadEditTask('Tache d\'un utilisateur non connecté');
        $this->addOrEditTask('Tache d\'un utilisateur non connecté (modified)','Tache d\'un utilisateur non connecté (modified)', false);

        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche a bien été modifiée.")')->count());
        $this->assertSame(1, $this->_crawler->filter('html:contains("Tache d\'un utilisateur non connecté (modified)")')->count());
    }

    public function testUserDeleteTask(): void
    {
        $this->init();
        $this->login('user1','user1');
        $this->deleteTask('Tache de user1');

        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche a bien été supprimée.")')->count());
        $this->assertSame(0, $this->_crawler->filter('html:contains("Tache de user1")')->count());
    }

    public function testUser1DeleteTaskOfUser2(): void
    {
        $this->init();
        $this->login('user1','user1');
        $this->deleteTask('Tache de user2');

        $this->assertSame(1, $this->_crawler->filter('html:contains("Vous ne pouvez pas supprimer une tâche créé par un autre utilisateur.")')->count());
        $this->assertSame(1, $this->_crawler->filter('html:contains("Tache de user2")')->count());
    }

    public function testAdminDeleteTask(): void
    {
        $this->init();
        $this->login('admin','admin');
        $this->deleteTask('Tache de admin');

        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche a bien été supprimée.")')->count());
        $this->assertSame(0, $this->_crawler->filter('html:contains("Tache de admin")')->count());
    }

    public function testAdminDeleteTaskOfAnonymous(): void
    {
        $this->init();
        $this->login('admin','admin');
        $this->deleteTask('Tache de anonymous');
        
        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche a bien été supprimée.")')->count());
        $this->assertSame(0, $this->_crawler->filter('html:contains("Tache de anonymous")')->count());
        $this->assertSame(1, $this->_crawler->filter('html:contains("Tache d\'un utilisateur non connecté")')->count());

        $form = $this->_crawler->filter('a:contains("Tache d\'un utilisateur non connecté")')->ancestors()->ancestors()->ancestors()->ancestors()->filter('button:contains("Supprimer")')->form();
        $this->_crawler = $this->_client->submit($form);
        $this->_client->followRedirects(); 
        $this->assertResponseIsSuccessful();

        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche a bien été supprimée.")')->count());
        $this->assertSame(0, $this->_crawler->filter('html:contains("Tache d\'un utilisateur non connecté")')->count());
    }

    public function testTaskMarkDone(): void
    {
        $this->init();
        $this->login('user1','user1');
        $link = $this->_crawler->selectLink('Consulter la liste des tâches à faire')->link();
        $this->_crawler = $this->_client->click($link);
        $this->_client->followRedirects();
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->_crawler->filter('html:contains("Tache de user1")')->count());
        $form = $this->_crawler->filter('a:contains("Tache de user1")')->ancestors()->ancestors()->ancestors()->ancestors()->filter('button:contains("Marquer comme faite")')->form();
        $this->_crawler = $this->_client->submit($form);
        $this->_client->followRedirects(); 
        $this->assertResponseIsSuccessful();
        
        $this->assertSame(1, $this->_crawler->filter('html:contains("La tâche Tache de user1 a bien modifier.")')->count());
    }

    public function init(): void
    {
        $this->_client = static::createClient();
    }

    public function login(String $username, String $password): void
    {
        $this->loadHomePage();
        $this->assertSame(1, $this->_crawler->filter('html:contains("Se connecter")')->count());
        $link = $this->_crawler->selectLink('Se connecter')->link();
        $this->_crawler = $this->_client->click($link);
        $this->_client->followRedirects();
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->_crawler->filter('html:contains("Mot de passe :")')->count());
        $form = $this->_crawler->selectButton('Se connecter')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $this->_crawler = $this->_client->submit($form);
        $this->_client->followRedirects();
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->_crawler->filter('html:contains("Se déconnecter")')->count());
    }

    public function addOrEditTask(String $title, String $content, Bool $addMode): void
    {
        if($addMode)
        {
            $form = $this->_crawler->selectButton('Ajouter')->form();
        }else
        {
            $form = $this->_crawler->selectButton('Modifier')->form();
        }

        $form['task[title]'] = $title;
        $form['task[content]'] = $content;
        $this->_crawler = $this->_client->submit($form);
        $this->_client->followRedirects();
        $this->assertResponseIsSuccessful();
    }

    public function deleteTask(String $taskTitle)
    {
        $link = $this->_crawler->selectLink('Consulter la liste des tâches à faire')->link();
        $this->_crawler = $this->_client->click($link);
        $this->_client->followRedirects();
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->_crawler->filter('html:contains("' . $taskTitle . '")')->count());
        $form = $this->_crawler->filter('a:contains("' . $taskTitle . '")')->ancestors()->ancestors()->ancestors()->ancestors()->filter('button:contains("Supprimer")')->form();
        $this->_crawler = $this->_client->submit($form);
        $this->_client->followRedirects(); 
        $this->assertResponseIsSuccessful();
    }

    public function loadHomePage(): void
    {
        $this->_crawler = $this->_client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }
    
    public function loadEditTask(String $taskTitle): void
    {
        $link = $this->_crawler->selectLink('Consulter la liste des tâches à faire')->link();
        $this->_crawler = $this->_client->click($link);
        $this->_client->followRedirects();
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->_crawler->filter('html:contains("' . $taskTitle .'")')->count());
        $link = $this->_crawler->selectLink($taskTitle)->link();
        $this->_crawler = $this->_client->click($link);
        $this->_client->followRedirects(); 
        $this->assertResponseIsSuccessful();
    }

    public function loadCreateNewTask(): void
    {
        $link = $this->_crawler->selectLink('Créer une nouvelle tâche')->link();
        $this->_crawler = $this->_client->click($link);
        $this->_client->followRedirects();
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->_crawler->filter('html:contains("Title")')->count());
        $this->assertSame(1, $this->_crawler->filter('html:contains("Content")')->count());
    }
}
