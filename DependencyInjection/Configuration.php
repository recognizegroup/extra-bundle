<?php

namespace Recognize\ExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
	Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Recognize\ExtraBundle\DependencyInjection
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('recognize_extra');
		$rootNode->children()
			->arrayNode('services')
				->children()
					->arrayNode('request_data')
						->children()
							->arrayNode('exclude_fields')
								->prototype('scalar')->defaultValue(array('password'))->end()
							->end()
						->end()
					->end()
				->end()
			->end()
		->end();
		return $treeBuilder;
	}

}
