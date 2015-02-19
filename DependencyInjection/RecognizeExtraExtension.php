<?php
namespace Recognize\ExtraBundle\DependencyInjection;

use Recognize\ExtraBundle\Utility\ArrayUtilities;
use Symfony\Component\DependencyInjection\ContainerBuilder,
	Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
	Symfony\Component\HttpKernel\DependencyInjection\Extension,
	Symfony\Component\Config\FileLocator;

/**
 * Class RecognizeExtraExtension
 * @package Recognize\ExtraBundle\DependencyInjection
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class RecognizeExtraExtension extends Extension {

	/**
	* @param array $configs
	* @param ContainerBuilder $container
	*/
	public function load(array $configs, ContainerBuilder $container) {
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		// Get configuration for services
		$services = ArrayUtilities::getColumnValue($config, 'services', array());
		$container->setParameter('recognize_extra.services.request_data', ArrayUtilities::getColumnValue($services, 'request_data', array()));


		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.xml');
	}

	/**
	* @return string
	*/
	public function getAlias() {
		return 'recognize_extra';
	}

}
