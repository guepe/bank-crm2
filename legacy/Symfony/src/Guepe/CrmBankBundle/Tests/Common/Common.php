<?php
namespace Guepe\CrmBankBundle\Tests\Common;
class Common {

	static public function addAccount($client) {
		// Create a new entry in the database
		$crawler = $client->request('GET', '/account/add');
		//$this->assertTrue(200 === $client->getResponse()->getStatusCode());

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
		return $crawler;

	}

	static public function testAddProduct($client, $productName, $crawler) {

		$link = $crawler->filter('a#add'.$productName.'product')->link();

		$crawler = $client->click($link);
		//$this->assertTrue(200 === $client->getResponse()->getStatusCode());

		$form = $crawler->selectButton('Enregistrer')
				->form(
						array(
								'guepe_crmbankbundle_'.$productName.'producttype[number]' => "123456"
						// ... other fields to fill
						));

		$client->submit($form);

		$crawler = $client->followRedirect();

		return ($crawler->filter('td:contains("123456")')->count() > 0);

	}

	
}
