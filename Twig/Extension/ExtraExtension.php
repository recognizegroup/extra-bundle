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
			new \Twig_SimpleFunction('queryString', function($prefix = '?') use ($requestStack) {
				return $prefix . $requestStack->getCurrentRequest()->getQueryString();
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