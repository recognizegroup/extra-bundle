<?php

namespace Recognize\ExtraBundle\Twig\Extension;

/**
 * Class ExtraExtension
 * @package Recognize\ExtraBundle\Twig\Extension
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class ExtraExtension extends \Twig_Extension {

	/**
	 * @return array
	 */
	public function getFilters() {
		return array('query_string' => new \Twig_Filter_Method($this, 'getQueryString'));
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