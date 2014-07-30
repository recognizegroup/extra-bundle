<?php

namespace Recognize\ExtraBundle\Twig\Extension;

use Symfony\Bridge\Doctrine\RegistryInterface,
	Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ExtraExtension
 * @package Recognize\ExtraBundle\Twig\Extension
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class ExtraExtension extends \Twig_Extension {

	/**
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	protected $requestStack;

	/**
	 * @var \Symfony\Bridge\Doctrine\RegistryInterface
	 */
	protected $registry;


	/**
	 * @param RequestStack $request
	 * @param \Symfony\Bridge\Doctrine\RegistryInterface $registry
	 */
	public function __construct(RequestStack $request, RegistryInterface $registry) {
		$this->requestStack = $request;
		$this->registry =  $registry;
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		$requestStack = $this->requestStack;

		return array(
			new \Twig_SimpleFunction('request', function($param) use ($requestStack) {
				$request = $requestStack->getCurrentRequest();
				return $request->get($param);
			}),
			new \Twig_SimpleFunction('queryString', function(array $filter = array(), $assoc = false) use ($requestStack) {
				$stack = $requestStack->getCurrentRequest()->query->all();
				$filtered = array_diff_key($stack, array_flip($filter));
				if(!empty($filtered) && !$assoc) {
					array_walk($filtered, function(&$item, $key) {
						$item = $key . '=' . $item;
					});
					return '?'.implode('&', $filtered);
				} elseif($assoc) { // When assoc mode
					return $filtered;
				} else return '';
			}),
			'repository' => new \Twig_Function_Method($this, 'getRepository')
		);
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		return array(
			'query_string' => new \Twig_Filter_Method($this, 'getQueryString'),
			'unset' => new \Twig_Filter_Method($this, 'unsetValue')
		);
	}

	/**
	 * @param array $array
	 * @param $value
	 * @return array
	 */
	public function unsetValue(array $array, $value) {
		if(isset($array[$value])) unset($array[$value]);
		return $array;
	}

	/**
	 * @param $repository
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getRepository($repository) {
		return $this->registry->getRepository($repository);
	}

	/**
	 * @param $queryString
	 * @return string
	 */
	public function getQueryString($queryString) {
		return ($queryString) ? '?'.$queryString : '';
	}

	/**
	 * @return string Name
	 */
	public function getName() {
		return 'recognize_extra.twig.extension';
	}

}