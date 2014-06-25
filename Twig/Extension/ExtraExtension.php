<?php

namespace Recognize\ExtraBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;

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
	 * @param RequestStack $request
	 */
	public function __construct(RequestStack $request) {
		$this->requestStack = $request;
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
			})
		);
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		return array(
			'query_string' => new \Twig_Filter_Method($this, 'getQueryString')
		);
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