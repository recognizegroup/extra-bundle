<?php

namespace Recognize\ExtraBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Class JSONResponse
 * @package Recognize\ExtraBundle\Configuration
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 *
 * @Annotation
 */
class JSONResponse extends ConfigurationAnnotation {

	/**
	 * @var array
	 */
	protected $vars = array();

	/**
	 * @return array
	 */
	public function getVars() {
		return $this->vars;
	}

	/**
	 * @param array $vars
	 */
	public function setVars(Array $vars) {
		$this->vars = $vars;
	}

	/**
	 * Only one JsonResponse is allowed
	 * @return bool
	 */
	public function allowArray() {
		return false;
	}

	/**
	 * @return string
	 */
	public function getAliasName() {
		return 'json';
	}

}