<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FonctionnelsUserTest extends WebTestCase
{
    public function testUserEdition(): void
    {
        //homepage
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Se connecter")')->count());
        $link = $crawler->selectLink('Se connecter')->link();
        $crawler = $client->click($link);
        $client->followRedirects();

        //Login Page
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Mot de passe :")')->count());
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'admin';
        $form['_password'] = 'admin';
        $crawler = $client->submit($form);
        $client->followRedirects();

        //homepage logged with admin account
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Modifier un utilisateur")')->count());
        $link = $crawler->selectLink('Modifier un utilisateur')->link();
        $crawler = $client->click($link);
        $client->followRedirects();

        //List of the users
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Liste des utilisateurs")')->count());
        //find the edit buttom for user1
        $usersTables = $crawler->filter('.container tbody');
        $user1Row = $usersTables->filter('tr:contains("user1")');
        $editLink = $user1Row->selectLink('Edit')->link();
        $crawler = $client->click($editLink);
        $client->followRedirects();

        //Edit page
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Modifier user1")')->count());
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'testChange';
        $form['user[password][first]'] = 'testChange';
        $form['user[password][second]'] = 'testChange';
        $form['user[email]'] = 'testChange@testChange.testChange';
        $form['user[roles][0]']->tick();
        $form['user[roles][1]']->untick();
        $crawler = $client->submit($form);
        $client->followRedirects();

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Superbe ! L\'utilisateur a bien été modifié")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("testChange")')->count());
    }

    public function testUserCreation(): void
    {
        //homepage
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Se connecter")')->count());
        $link = $crawler->selectLink('Se connecter')->link();
        $crawler = $client->click($link);
        $client->followRedirects();

        //Login Page
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Mot de passe :")')->count());
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'admin';
        $form['_password'] = 'admin';
        $crawler = $client->submit($form);
        $client->followRedirects();

        //homepage logged with admin account
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Créer un utilisateur")')->count());
        $link = $crawler->selectLink('Créer un utilisateur')->link();
        $crawler = $client->click($link);
        $client->followRedirects();

        //User creation page
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("Créer un utilisateur")')->count());
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'testAdd';
        $form['user[password][first]'] = 'testAdd';
        $form['user[password][second]'] = 'testAdd';
        $form['user[email]'] = 'testAdd@testAdd.testAdd';
        $form['user[roles][0]']->tick();
        $form['user[roles][1]']->untick();
        $crawler = $client->submit($form);
        $client->followRedirects();

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('html:contains("L\'utilisateur a bien été ajouté.")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("testAdd")')->count());
    }
}
