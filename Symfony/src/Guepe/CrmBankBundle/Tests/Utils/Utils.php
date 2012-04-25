<?php

namespace Guepe\CrmBankBundle\Tests\Utils;


class Utils {
	static public function CleanDB($client) {
		$crawler = $client->request('GET', '/CleanDB');
		return $client->getResponse()->getStatusCode();
	}
}
