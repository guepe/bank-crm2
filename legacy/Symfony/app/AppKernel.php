<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {
	public function registerBundles() {
		$bundles = array(new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
				new Symfony\Bundle\SecurityBundle\SecurityBundle(),
				new Symfony\Bundle\TwigBundle\TwigBundle(),
				new Symfony\Bundle\MonologBundle\MonologBundle(),
				new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
				new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
				new Symfony\Bundle\AsseticBundle\AsseticBundle(),
				new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
				new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
				new Guepe\CrmBankBundle\GuepeCrmBankBundle(),
				new FOS\UserBundle\FOSUserBundle(),
				new Guepe\UserBundle\GuepeUserBundle(),
				new Zenstruck\Bundle\MobileBundle\ZenstruckMobileBundle(),);

		if (in_array($this->getEnvironment(), array('dev', 'test'))) {
			$bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
			$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
		}

		if ('test' === $this->getEnvironment()) {
			$bundles[] = new Behat\BehatBundle\BehatBundle();
			$bundles[] = new Behat\MinkBundle\MinkBundle();

		}

		if ('test' === $this->getEnvironment()) {
			// don't autoload Symfony2 classes, as they are
			// already loaded by the Symfony2 itself
			if (!defined('BEHAT_AUTOLOAD_SF2'))
				define('BEHAT_AUTOLOAD_SF2', false);
			require_once 'behat/autoload.php';
			require_once 'mink/autoload.php';

		}

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader) {
		$loader
				->load(
						__DIR__ . '/config/config_' . $this->getEnvironment()
								. '.yml');
	}
}
