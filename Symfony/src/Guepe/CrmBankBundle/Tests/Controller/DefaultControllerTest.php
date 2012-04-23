<?php

namespace Guepe\CrmBankBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase {
	public function testIndex() {
		$client = static::createClient();

		$crawler = $client->request('GET', '/');

		$this
				->assertTrue(
						$crawler->filter('html:contains("A venir")')->count()
								> 0);
	}
}
