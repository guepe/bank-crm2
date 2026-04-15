<?php

namespace Guepe\CrmBankBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Guepe\CrmBankBundle\Tests\Utils\Utils;
use Guepe\CrmBankBundle\Tests\Common\Common;

class BankProductControllerTest extends WebTestCase {

	public function testAddAccount() {
		// Create a new client to browse the application
		$client = static::createClient();

		$this->assertTrue(200 == Utils::CleanDB($client));

		// Create a new entry in the database
		$crawler = $client->request('GET', '/account/add');
		$this->assertTrue(200 === $client->getResponse()->getStatusCode());

		// Fill in the form and submit it
		$form = $crawler->selectButton('Enregistrer')
				->form(
						array('account[name]' => 'Test',
								'account[company_statut]' => 'Personne physique',
								'account[type]' => 'Core',
								'account[starting_date]' => '20/12/2012',
								'account[otherbank]' => 'Dexia',
								'account[streetnum]' => 'Rue de romsée, 48',
								'account[zip]' => '4565',
								'account[city]' => 'Liege',
								'account[country]' => 'BE',
								'account[notes]' => 'bla bla'
						// ... other fields to fill
						));

		$client->submit($form);

		$crawler = $client->followRedirect();

		$this
				->assertTrue(
						$crawler->filter('span:contains("Test")')->count() > 0);

		$this->assertTrue(200 == Utils::CleanDB($client));

	}

	public function testAddAccountAddContact() {
		// Create a new client to browse the application
		$client = static::createClient();

		$this->assertTrue(200 == Utils::CleanDB($client));

		// Create a new entry in the database
		$crawler = $client->request('GET', '/contact/add');
		$this->assertTrue(200 === $client->getResponse()->getStatusCode());

		// Fill in the form and submit it
		$form = $crawler->selectButton('Enregistrer')
				->form(
						array('contact[lastname]' => 'Test',
								'contact[firstname]' => 'test tes',
								'contact[marital_status]' => 'single',
								'contact[street_num]' => 'Rue de romsée, 48',
								'contact[zip]' => '4565',
								'contact[city]' => 'Liege',
								'contact[country]' => 'BE',
								'contact[email]' => 'nospam@hoe.be',
								'contact[phone]' => '989898',
								'contact[phone2]' => '989898',
								'contact[birthplace]' => 'chenee',
								'contact[gsm]' => '9898989'
						// ... other fields to fill
						));

		$client->submit($form);

		$crawler = $client->followRedirect();
		$this
				->assertTrue(
						$crawler->filter('span:contains("Test")')->count() > 0);

		// Create a new entry in the database
		$crawler = $client->request('GET', '/account/add');
		$this->assertTrue(200 === $client->getResponse()->getStatusCode());

		// Fill in the form and submit it
		$form = $crawler->selectButton('Enregistrer')
				->form(
						array('account[name]' => 'Test',
								'account[company_statut]' => 'Personne physique',
								'account[type]' => 'Core',
								'account[starting_date]' => '20/12/2012',
								'account[otherbank]' => 'Dexia',
								'account[streetnum]' => 'Rue de romsée, 48',
								'account[zip]' => '4565',
								'account[city]' => 'Liege',
								'account[country]' => 'BE',
								'account[notes]' => 'bla bla'
						// ... other fields to fill
						));

		$client->submit($form);

		$crawler = $client->followRedirect();

		$this
				->assertTrue(
						$crawler->filter('span:contains("Test")')->count() > 0);

		//<a href="/app_dev.php/contact/247"><i class="icon-plus"></i> Ajouter</a>
		$this
				->assertEquals(" Ajouter",
						$crawler->filter('a#addcontact')->text());

		$link = $crawler->filter('a#addcontact')->link();

		$crawler = $client->click($link);
		$this->assertTrue(200 === $client->getResponse()->getStatusCode());

		$this
				->assertTrue(
						$crawler->filter('a:contains("Test")')->count() > 0);

		$link = $crawler->filter('a:contains("Selectionner")')->link();

		$crawler = $client->click($link);

		$crawler = $client->followRedirect();

		$this
				->assertTrue(
						$crawler->filter('td:contains("test tes")')->count()
								> 0);

		$this->assertTrue(200 == Utils::CleanDB($client));

	}

	public function testAddContact() {
		// Create a new client to browse the application
		$client = static::createClient();

		$this->assertTrue(200 == Utils::CleanDB($client));

		// Create a new entry in the database
		$crawler = $client->request('GET', '/contact/add');
		$this->assertTrue(200 === $client->getResponse()->getStatusCode());

		// Fill in the form and submit it
		$form = $crawler->selectButton('Enregistrer')
				->form(
						array('contact[lastname]' => 'Test',
								'contact[firstname]' => 'test tes',
								'contact[marital_status]' => 'single',
								'contact[street_num]' => 'Rue de romsée, 48',
								'contact[zip]' => '4565',
								'contact[city]' => 'Liege',
								'contact[country]' => 'BE',
								'contact[email]' => 'nospam@hoe.be',
								'contact[phone]' => '989898',
								'contact[phone2]' => '989898',
								'contact[birthplace]' => 'chenee',
								'contact[gsm]' => '9898989'
						// ... other fields to fill
						));

		$client->submit($form);

		$crawler = $client->followRedirect();
		$this
				->assertTrue(
						$crawler->filter('span:contains("Test")')->count() > 0);

		$this->assertTrue(200 == Utils::CleanDB($client));

	}

	public function testAddAccountAddProduct() {
		// Create a new client to browse the application
		$client = static::createClient();

		$this->assertTrue(200 == Utils::CleanDB($client));

		$crawler = Common::addAccount($client);

		$this
				->assertTrue(
						$crawler->filter('span:contains("Test")')->count() > 0);

		// Optionnal assert
		//	$this
		//		->assertEquals(" Ajouter",
		//				$crawler->filter('a#addcreditproduct')->text());

		$this->assertTrue(Common::testAddProduct($client, "credit", $crawler));
		$this->assertTrue(Common::testAddProduct($client, "bank", $crawler));
		$this->assertTrue(Common::testAddProduct($client, "savings", $crawler));
		$this->assertTrue(Common::testAddProduct($client, "fiscal", $crawler));

		$this->assertTrue(200 == Utils::CleanDB($client));

	}

}
