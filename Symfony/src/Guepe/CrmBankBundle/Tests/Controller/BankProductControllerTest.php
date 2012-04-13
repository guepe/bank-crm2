<?php

namespace Guepe\CrmBankBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BankProductControllerTest extends WebTestCase
{
    
    public function testAddAccount()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        
        
        // Create a new entry in the database
        $crawler = $client->request('GET', '/account/edit');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
		
		/*
        
        // Fill in the form and submit it
        $form = $crawler->selectButton('Enregistrer')->form(array(
            'account[name]'  => 'Test',
            // ... other fields to fill
        ));

        
        $client->submit($form);
        
        $crawler = $client->followRedirect();

        
        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("Test")')->count() > 0);

        /*
        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Edit')->form(array(
            'account[name]'  => 'Foo',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertTrue($crawler->filter('[value="Foo"]')->count() > 0);
		*/
        // Delete the entity
       // $client->submit($crawler->selectButton('Delete')->form());
        //$crawler = $client->followRedirect();

        // Check the entity has been delete on the list
        //$this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
    }
    
}